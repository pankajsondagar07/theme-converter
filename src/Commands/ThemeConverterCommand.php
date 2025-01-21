<?php

namespace Pankaj\ThemeConverter\Commands;

use Illuminate\Console\Command;

class ThemeConverterCommand extends Command
{
    public $signature = 'theme-converter';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
