<?php

namespace RuslanShigabutdinov\NativeColorScheme\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null get()
 * @method static string|null getColorScheme()
 * @method static string|null getSystemColorScheme()
 * @method static string|null getPreference()
 * @method static array|null state()
 * @method static bool setPreference(string $preference)
 * @method static bool useSystem()
 * @method static bool useLight()
 * @method static bool useDark()
 * @method static bool isSystem()
 * @method static bool isSystemDark()
 * @method static bool isSystemLight()
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
