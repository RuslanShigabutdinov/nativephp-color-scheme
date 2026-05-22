<?php

namespace RuslanShigabutdinov\NativeColorScheme\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null get()
 * @method static bool isDark()
 * @method static bool isLight()
 *
 * @see \RuslanShigabutdinov\NativeColorScheme\NativeColorScheme
 */
class NativeColorScheme extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RuslanShigabutdinov\NativeColorScheme\NativeColorScheme::class;
    }
}
