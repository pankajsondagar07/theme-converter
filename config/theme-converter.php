<?php

return [
    'paths' => [
        'temp' => storage_path('app/temp'),
        'views' => resource_path('views/themes'),
        'assets' => public_path('themes'),
    ],

    'exclude' => [
        '*.txt',
        '*.md',
        'LICENSE',
        'README*',
    ],

    'partials' => [
        'header',
        'footer',
        'sidebar',
        'navigation',
        'head',
    ],
    'layout' => [
        'files' => [
            'layout',
            'master',
            'template',
            'main',
            'wrapper',
            'app'
        ],
        
        'markers' => [
            '<!DOCTYPE html>',
            '<html',
            '<head>',
            '</head>',
            '<body>',
            '</body>',
            '</html>'
        ],
        
        'sections' => [
            'head' => ['<head', '</head>'],
            'header' => ['<header', '</header>', 'id="header"', 'class="header"'],
            'footer' => ['<footer', '</footer>', 'id="footer"', 'class="footer"'],
            'sidebar' => ['<aside', '</aside>', 'id="sidebar"', 'class="sidebar"'],
            'content' => ['<main', '</main>', 'id="content"', 'id="main"', 'class="content"', 'class="main"']
        ],
        
        'filename' => 'layout.blade.php',
        
        'directory' => 'layouts'
    ]
];