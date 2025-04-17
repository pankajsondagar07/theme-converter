<?php

namespace Pankaj\ThemeConverter\Commands;

use Illuminate\Console\Command;
use Pankaj\ThemeConverter\ThemeConverter;

class ThemeConverterCommand extends Command
{
    protected $signature = 'theme:convert 
                        {zip : Path to the theme zip file}
                        {--name= : Custom name for the theme}
                        {--no-layout : Disable automatic layout detection}';

    protected $description = 'Convert a HTML theme to Laravel Blade templates';

    public function handle(ThemeConverter $converter)
    {
        $zipPath = $this->argument('zip');
        $themeName = $this->option('name');
        $noLayout = $this->option('no-layout');

        if (!file_exists($zipPath)) {
            $this->error("Zip file not found: {$zipPath}");
            return 1;
        }

        $this->info("Converting theme..." . ($noLayout ? ' (layout detection disabled)' : ''));

        try {
            if ($noLayout) {
                config(['theme-converter.layout' => null]);
            }

            $outputPath = $converter->convert($zipPath, $themeName);
            
            if (!$noLayout && $converter->hasDetectedLayout()) {
                $this->info("✔ Layout detected and converted");
                $this->line("  Layout file: {$outputPath}/layouts/layout.blade.php");
                $this->line("  Pages will extend this layout automatically");
            }

            $this->info("Theme converted successfully!");
            $this->line("Blade files created at: {$outputPath}");
            $this->line("Assets copied to: " . public_path('themes/' . ($themeName ?? pathinfo($zipPath, PATHINFO_FILENAME))));
        } catch (\Exception $e) {
            $this->error("Conversion failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}