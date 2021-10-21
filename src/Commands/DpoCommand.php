<?php

namespace Zepson\Dpo\Commands;

use Illuminate\Console\Command;

class DpoCommand extends Command
{
    public $signature = 'dpo-laravel';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
