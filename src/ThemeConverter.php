<?php

namespace Pankaj\ThemeConverter;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ThemeConverter
{
    protected $config;

    protected $themeName;

    protected $tempPath;

    protected $outputPath;

    public function __construct()
    {
        $this->config = config('theme-converter');
    }

    public function convert($zipPath, $themeName = null)
    {
        $this->themeName = $themeName ?? pathinfo($zipPath, PATHINFO_FILENAME);
        $this->tempPath = storage_path('app/temp/theme-'.time());
        $this->outputPath = resource_path('views/themes/'.$this->themeName);

        $this->extractZip($zipPath);
        $this->processFiles();
        $this->cleanUp();

        return $this->outputPath;
    }

    protected function cleanUp()
    {
        File::deleteDirectory($this->tempPath);
    }

    protected function extractZip($zipPath)
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            File::ensureDirectoryExists($this->tempPath);
            $zip->extractTo($this->tempPath);
            $zip->close();

            return true;
        }
        throw new \Exception('Failed to extract zip file');
    }

    protected function processFiles()
    {
        File::ensureDirectoryExists($this->outputPath);

        $files = File::allFiles($this->tempPath);

        foreach ($files as $file) {
            if ($this->isHtmlFile($file)) {
                $this->convertToBlade($file);
            } elseif ($this->isAssetFile($file)) {
                $this->copyAsset($file);
            }
        }
    }

    protected function isHtmlFile($file)
    {
        return in_array(strtolower($file->getExtension()), ['html', 'htm']);
    }

    protected function isAssetFile($file)
    {
        $assetExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'woff', 'woff2', 'ttf', 'eot'];

        return in_array(strtolower($file->getExtension()), $assetExtensions);
    }

    protected function convertToBlade($file)
    {
        $relativePath = str_replace($this->tempPath, '', $file->getPath());
        $outputDir = $this->outputPath.$relativePath;
        File::ensureDirectoryExists($outputDir);

        $bladeName = str_replace(['.html', '.htm'], '.blade.php', $file->getFilename());
        $outputPath = $outputDir.'/'.$bladeName;

        $content = File::get($file->getPathname());

        if ($this->isLikelyLayoutFile($file, $content)) {
            $convertedContent = $this->convertToMasterLayout($content);
            $bladeName = 'layout.blade.php';
            $outputPath = $this->outputPath.'/layouts/'.$bladeName;
            File::ensureDirectoryExists($this->outputPath.'/layouts');
        } else {
            $convertedContent = $this->convertContentToBlade($content);

            if ($this->hasDetectedLayout()) {
                $convertedContent = "@extends('themes.{$this->themeName}.layouts.layout')\n\n@section('content')\n".
                                    $convertedContent."\n@endsection";
            }
        }

        File::put($outputPath, $convertedContent);
    }

    protected function isLikelyLayoutFile($file, $content)
    {
        $filename = strtolower($file->getFilename());

        $layoutFiles = ['layout', 'master', 'template', 'main', 'wrapper'];
        foreach ($layoutFiles as $pattern) {
            if (Str::contains($filename, $pattern)) {
                return true;
            }
        }

        $commonLayoutMarkers = [
            '<!DOCTYPE html>',
            '<html',
            '<head>',
            '</head>',
            '<body>',
            '</body>',
            '</html>',
        ];

        $markerCount = 0;
        foreach ($commonLayoutMarkers as $marker) {
            if (Str::contains($content, $marker)) {
                $markerCount++;
            }
        }

        return $markerCount >= 3;
    }

    protected function convertToMasterLayout($content)
    {
        $content = $this->convertContentToBlade($content);

        $sections = [
            'head' => ['<head', '</head>'],
            'header' => ['<header', '</header>', 'id="header"', 'class="header"'],
            'footer' => ['<footer', '</footer>', 'id="footer"', 'class="footer"'],
            'sidebar' => ['<aside', '</aside>', 'id="sidebar"', 'class="sidebar"'],
            'content' => ['<main', '</main>', 'id="content"', 'id="main"', 'class="content"', 'class="main"'],
        ];

        foreach ($sections as $section => $markers) {
            $startPos = $endPos = null;

            foreach ($markers as $marker) {
                $pos = stripos($content, $marker);
                if ($pos !== false) {
                    if (Str::startsWith($marker, '</')) {
                        $endPos = $pos + strlen($marker);
                    } else {
                        $startPos = $pos;
                    }
                }
            }

            if ($startPos !== null && $endPos !== null) {
                $sectionContent = substr($content, $startPos, $endPos - $startPos);
                $newSection = "@section('{$section}')\n{$sectionContent}\n@endsection";
                $content = substr_replace($content, $newSection, $startPos, $endPos - $startPos);
            }
        }

        if (! Str::contains($content, "@section('content')")) {
            $bodyStart = stripos($content, '<body');
            $bodyEnd = stripos($content, '</body>');

            if ($bodyStart !== false && $bodyEnd !== false) {
                $bodyEnd += 7;
                $bodyContent = substr($content, $bodyStart, $bodyEnd - $bodyStart);
                $newBody = "@section('content')\n{$bodyContent}\n@endsection";
                $content = substr_replace($content, $newBody, $bodyStart, $bodyEnd - $bodyStart);
            }
        }

        return $content;
    }

    protected function hasDetectedLayout()
    {
        return file_exists($this->outputPath.'/layouts/layout.blade.php');
    }

    protected function convertContentToBlade($content)
    {
        // Convert asset paths
        $content = preg_replace_callback('/(src|href)=["\']([^"\']+)["\']/', function ($matches) {
            $url = $matches[2];

            // Skip if already a Laravel asset or URL
            if (Str::startsWith($url, ['{{', 'http://', 'https://'])) {
                return $matches[0];
            }

            // Convert to asset path
            $newUrl = "{{ asset('themes/{$this->themeName}".(Str::startsWith($url, '/') ? $url : '/'.$url)."') }}";

            return $matches[1].'="'.$newUrl.'"';
        }, $content);

        // Convert includes for common template parts
        $content = preg_replace([
            '/<!--\s*#include\s+file="([^"]+)"\s*-->/i',
            '/<\?php\s+include\s*\(?\s*[\'"]([^\'"]+)[\'"]\s*\)?\s*;\s*\?>/i',
        ], '@include(\'themes.'.$this->themeName.'.$1\')', $content);

        // Convert simple PHP tags to Blade
        $content = preg_replace([
            '/<\?=\s*([^\?]+)\s*\?>/',
            '/<\?php\s+echo\s+([^;]+);\s*\?>/',
        ], '{{ $1 }}', $content);

        // Convert PHP blocks to Blade
        $content = preg_replace('/<\?php(.*?)\?>/s', '@php$1@endphp', $content);

        return $content;
    }

    protected function copyAsset($file)
    {
        $relativePath = str_replace($this->tempPath, '', $file->getPath());
        $outputDir = public_path('themes/'.$this->themeName.$relativePath);
        File::ensureDirectoryExists($outputDir);

        File::copy($file->getPathname(), $outputDir.'/'.$file->getFilename());
    }
}
