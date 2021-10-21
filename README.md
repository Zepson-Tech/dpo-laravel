# DPO (Direct Pay Online) Laravel Package
## _The best DPO Laravel package, simple Ever_


[![Build Status](https://travis-ci.org/joemccann/dillinger.svg?branch=master)](https://travis-ci.org/joemccann/dillinger)

This is the package that will help you add DPO Payment API to your Laravel Application, We are open to contribution and support to make it better.

## Features

- Create Payment Token (Initiate order at DPO servers)
- Verify Payment Token (Check Transaction status)
- Make Direct payment(Redirects order direct to DPO payment page)
- Fetch Payment Token
- Database migrations to save transaction details
- Much easy to use
- Constantly updated

## How It works
1. Create payment token
2. Verify token
3. Redirect to DPO payment page
4. Fetch response
5. Done 
 
#### The package does all this for you behind the scenes😂
Basic Usage Example.

```php
<?php 
use Zepson\Dpo\Dpo;
........

  $dpo = new Dpo();
   $order = [
            'paymentAmount'=>"10000",
            'paymentCurrency'=> "TZS",
            'customerFirstName'=> "Novath",
            'customerLastName'=> "Thomas",
            'customerAddress'=> "Tanzania",
             'customerCity'=>"Dodoma",
            'customerPhone'=> "0752771650",
            'customerEmail'=> "novath@zepson.co.tz",
            'companyRef'=> "34TESTREFF"
        ];
    //Now make  payment
    $dpo->directPayment($data);
    //Its done!  Simple right!😂

```

## Installation

Install the package Via Composer 
```sh
    composer require zepson/dpo-laravel
```
 Now You can Publish the configuration File and Migration
```sh
  php artisan vendor:publish --provider="Zepson\Dpo\DpoServiceProvider" --tag="dpo-laravel-migrations"
```

   Migrate The Database table[OPTIONAL]
```php
  php artisan migrate
```

#### Configuration File'

```php
<?php

return [
    "company_token"=>env("DPO_COMPANY_TOKEN","9F416C11-127B-4DE2-AC7F-D5710E4C5E0A"),
    "account_type"=>env("DPO_ACCOUNT_TYPE","3854"),
    'is_test_mode'=>env("DPO_IS_TEST_MODE",true),
    "back_url"=>env("DPO_BACK_URL"),
    "redirect_url"=>env("DPO_REDIRECT_URL")
];
```
The above Details are for Test/Sandbox environment taken from DPO public documentation
#### Update .env File with Correct Informations
```
DPO_COMPANY_TOKEN = "YOUR_DPO_COMPANY_TOKEN"
DPO_ACCOUNT_TYPE = "YOUR_DPO_ACCONT_TYPE"
DPO_IS_TEST_MODE = "true/false"
DPO_BACK_URL ="YOUR APPLICATION_BACK_URL"
DPO_REDIRECT_URL =  "YAPPLICATION_REDIRECT_URL_AFTER_RESPONSE_FROM_DPO"
```
## USAGE
- create array of your order which match  parrameters in the following exaple
```php
$order = [
            'paymentAmount'=>"10000",
            'paymentCurrency'=> "TZS",
            'customerFirstName'=> "Novath",
            'customerLastName'=> "Thomas",
            'customerAddress'=> "Tanzania",
             'customerCity'=>"Dodoma",
            'customerPhone'=> "0752771650",
            'customerEmail'=> "novath@zepson.co.tz",
            'companyRef'=> "34TESTREFF"
        ];
```
- Now you can choose to make direct payment or createToken First and then make payment
- Starting with Direct Payment
```php
   $dpo = new Dpo;
   return $dpo->directPayment($data); //this will redirect user to DPO Payment page
 ```
 - If you preffer to save details then this is the ideal step to follow [Generate Token, Make payment]
 - Get Token
 ```php
       $token = $dpo->createToken($data); //return array of response with transaction code
       //you can save or do what ever you want with the response
```
- Get payment Url
```php
    $dpo->getPaymentUrl($token);
```
- Redirect User to payment page
```php
   return Redirect::to($payment_url);
```

As [Novath Thomas] always says

> There is a huge difference between sysem security and
>complications, Complication hurts, API should'nt be complicated
>Thats one of the primary AIM of the introduction of APIs


#### We would like more contributions to make the package more secure and readable.

## License

MIT

