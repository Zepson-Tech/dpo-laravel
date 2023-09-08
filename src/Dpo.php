<?php

namespace Zepson\Dpo;

use Illuminate\Support\Facades\Redirect;

/**
 * Dpo
 * @author Novath Thomas <ujumbe@zepson.co.tz>
 */
class Dpo
{
    const DPO_URL_TEST = 'https://secure1.sandbox.directpay.online';
    const DPO_URL_LIVE = 'https://secure.3gdirectpay.com';

    private $dpoUrl;
    private $dpoGateway;
    private $testMode = false;
    private $company_token;
    private $company_type;
    private $dpo_back_url;
    private $dpo_redirect_url;

    /**
     * Dpo constructor.
     */
    public function __construct()
    {
        $this->testMode = config("dpo-laravel.is_test_mode");
        $this->company_token = config("dpo-laravel.company_token");
        $this->company_type = config("dpo-laravel.account_type");
        $this->dpo_back_url = config("dpo-laravel.back_url");
        $this->dpo_redirect_url = config("dpo-laravel.redirect_url");
        if ($this->testMode) {
            $this->dpoUrl = self::DPO_URL_TEST;
        } else {
            $this->dpoUrl = self::DPO_URL_LIVE;
        }
        $this->dpoGateway = $this->dpoUrl . '/payv2.php?ID=';
    }

    /**
     * getDpoGateway
     */
    public function getDpoGateway()
    {
        return $this->dpoGateway;
    }

    /**
     * Create a DPO token for payment processing
     * @param array $data
     * @return array
     */
    public function createToken(array $data)
    {


        $companyToken      = $this->company_token;
        $accountType       =  $this->company_type;
        $paymentAmount     = $data['paymentAmount'];
        $paymentCurrency   = $data['paymentCurrency'];
        $customerFirstName = $data['customerFirstName'];
        $customerLastName  = $data['customerLastName'];
        $customerAddress   = $data['customerAddress'];
        $customerCity      = $data['customerCity'];
        $customerPhone     = $data['customerPhone'];
        $redirectURL       = $this->dpo_redirect_url;
        $backURL           = $this->dpo_back_url;
        $customerEmail     = $data['customerEmail'];
        $reference         = $data['companyRef'];

        $odate   = date('Y/m/d H:i');
        $postXml = <<<POSTXML
        <?xml version="1.0" encoding="utf-8"?>
        <API3G>
        <CompanyToken>$companyToken</CompanyToken>
        <Request>createToken</Request>
        <Transaction>
        <PaymentAmount>$paymentAmount</PaymentAmount>
        <PaymentCurrency>$paymentCurrency</PaymentCurrency>
        <CompanyRef>$reference</CompanyRef>
        <customerFirstName>$customerFirstName</customerFirstName>
        <customerLastName>$customerLastName</customerLastName>
        <customerAddress>$customerAddress</customerAddress>
        <customerCity>$customerCity</customerCity>
        <customerPhone>$customerPhone</customerPhone>
        <RedirectURL>$redirectURL</RedirectURL>
        <BackURL>$backURL</BackURL>
        <customerEmail>$customerEmail</customerEmail>
        <TransactionSource>whmcs</TransactionSource>
        </Transaction>
        <Services>
        <Service>
        <ServiceType>$accountType</ServiceType>
        <ServiceDescription>$reference</ServiceDescription>
        <ServiceDate>$odate</ServiceDate>
        </Service>
        </Services>
        </API3G>
POSTXML;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->dpoUrl . "/API/v6/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $postXml,
            CURLOPT_HTTPHEADER     => array(
                "cache-control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $error    = curl_error($curl);

        curl_close($curl);

        if ($response != '') {
            $xml               = new \SimpleXMLElement($response);
            $result            = $xml->xpath('Result')[0]->__toString();
            $resultExplanation = $xml->xpath('ResultExplanation')[0]->__toString();
            $returnResult      = [
                'result'            => $result,
                'resultExplanation' => $resultExplanation,
            ];

            // Check if token was created successfully
            if ($xml->xpath('Result')[0] != '000') {
                $returnResult['success'] = 'false';
            } else {
                $transToken                 = $xml->xpath('TransToken')[0]->__toString();
                $transRef                   = $xml->xpath('TransRef')[0]->__toString();
                $returnResult['success']    = 'true';
                $returnResult['transToken'] = $transToken;
                $returnResult['transRef']   = $transRef;
            }
            return $returnResult;
        } else {
            return [
                'success'           => false,
                'result'            => !empty($error) ? $error : 'Unknown error occurred in token creation',
                'resultExplanation' => !empty($error) ? $error : 'Unknown error occurred in token creation',
            ];
        }
    }

    /**
     * Verify the DPO token created in first step of transaction
     * @param array $data
     * @return bool|string
     */
    public function verifyToken(array $data)
    {
        $companyToken = $data['companyToken'];
        $transToken   = $data['transToken'];

        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL            => $this->dpoUrl . "/API/v6/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<API3G>\r\n  <CompanyToken>" . $companyToken . "</CompanyToken>\r\n  <Request>verifyToken</Request>\r\n  <TransactionToken>" . $transToken . "</TransactionToken>\r\n</API3G>",
                CURLOPT_HTTPHEADER     => array(
                    "cache-control: no-cache",
                ),
            ));

            $response = curl_exec($curl);
            $err      = curl_error($curl);

            curl_close($curl);

            if (strlen($err) > 0) {
                echo "cURL Error #:" . $err;
            } else {
                return $response;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * getPaymentUrl
     */
    public function getPaymentUrl(array $data)
    {
        $dpo = new Dpo;
        if ($data['success'] === 'true') {

            $verify   = $dpo->verifyToken(["companyToken" => config("dpo-laravel.company_token"), "transToken" => $data['transToken']]);

            if (!empty($verify) && $verify != '') {
                $verify = new \SimpleXMLElement($verify);

                if ($verify->Result->__toString() === '900') {
                    $payUrl = $dpo->getDpoGateway() . $data['transToken'];
                    // redirect($payUrl);
                    return $payUrl;
                }
            }
        } else {
            echo 'Something went wrong: ' . $data['resultExplanation'];
            $url = env("APP_URL") . 'viewinvoice.php?id=' . $data['companyRef'];
            echo <<<HTML
<br><br><a href="$url">Click here to return</a>
HTML;
        }
    }


    /**
     * directPayment
     */
    public function directPayment(array $data)
    {
        $get_payment_token = $this->createToken($data);

        $payment_url = $this->getPaymentUrl($get_payment_token);

        return Redirect::to($payment_url);
    }
}
