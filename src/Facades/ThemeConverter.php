<?php

namespace Pankaj\ThemeConverter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Pankaj\ThemeConverter\ThemeConverter
 */
class ThemeConverter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Pankaj\ThemeConverter\ThemeConverter::class;
    }
}
