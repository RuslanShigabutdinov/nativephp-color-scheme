# NativePHP Color Scheme

NativePHP Mobile plugin for reading and controlling the app color scheme from native Android and iOS code.

## Platform Status

- Android: tested on device.
- iOS: implemented, but still needs device/simulator validation.

It supports three user preferences:

- `system`
- `light`
- `dark`

The effective color scheme is always:

- `light`
- `dark`

When the preference is `system`, the effective color scheme follows the native runtime's current system appearance.

## Requirements

- PHP 8.3+
- NativePHP Mobile 3.3+
- Android API 21+
- iOS 15+

## Installation

Install the package:

```bash
composer require ruslanshigabutdinov/nativephp-color-scheme
```

Publish the NativePHP plugin provider if your app does not have one yet:

```bash
php artisan vendor:publish --tag=nativephp-plugins-provider
```

Register the plugin:

```bash
php artisan native:plugin:register ruslanshigabutdinov/nativephp-color-scheme
```

Verify that NativePHP sees the plugin:

```bash
php artisan native:plugin:list
```

You should see:

```text
NativeColorScheme.Get
NativeColorScheme.SetPreference
```

Because this package contains native Kotlin and Swift code, rebuild your NativePHP app after installation:

```bash
php artisan native:run
```

For a full native project refresh:

```bash
php artisan native:install --force
php artisan native:run
```

## GitHub Development Install

If the package is not available on Packagist yet, install it from GitHub as a VCS repository.

Add the repository to your app's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/RuslanShigabutdinov/nativephp-color-scheme"
        }
    ]
}
```

Then require the package:

```bash
composer require ruslanshigabutdinov/nativephp-color-scheme:dev-main
```

## Usage

```php
use RuslanShigabutdinov\NativeColorScheme\Facades\NativeColorScheme;

NativeColorScheme::getPreference();  // 'system', 'light', 'dark', or null
NativeColorScheme::getColorScheme(); // effective 'light', 'dark', or null
NativeColorScheme::getSystemColorScheme(); // system 'light', 'dark', or null
NativeColorScheme::get();            // alias for getColorScheme()

NativeColorScheme::isSystem(); // true when preference is system
NativeColorScheme::isSystemDark();
NativeColorScheme::isSystemLight();
NativeColorScheme::isDark();   // true when effective scheme is dark
NativeColorScheme::isLight();  // true when effective scheme is light
```

Set the app preference:

```php
NativeColorScheme::useSystem();
NativeColorScheme::useLight();
NativeColorScheme::useDark();

NativeColorScheme::setPreference('system');
NativeColorScheme::setPreference('light');
NativeColorScheme::setPreference('dark');
```

`auto`, `device`, and `os` are accepted aliases for `system`:

```php
NativeColorScheme::setPreference('auto');
```

Read the full state:

```php
NativeColorScheme::state();

// [
//     'preference' => 'system',
//     'colorScheme' => 'dark',
//     'systemColorScheme' => 'dark',
//     'isSystem' => true,
//     'isDark' => true,
//     'isLight' => false,
// ]
```

You can also resolve the service directly:

```php
use RuslanShigabutdinov\NativeColorScheme\NativeColorScheme;

app(NativeColorScheme::class)->getColorScheme();
```

## How It Works

The PHP API calls NativePHP bridge methods:

```php
nativephp_call('NativeColorScheme.Get', '{}');
nativephp_call('NativeColorScheme.SetPreference', '{"preference":"dark"}');
```

The methods are declared in `nativephp.json`:

```json
[
    {
        "name": "NativeColorScheme.Get",
        "android": "com.ruslanshigabutdinov.nativecolorscheme.NativeColorSchemeFunctions.Get",
        "android_params": ["context"],
        "ios": "NativeColorSchemeFunctions.Get"
    },
    {
        "name": "NativeColorScheme.SetPreference",
        "android": "com.ruslanshigabutdinov.nativecolorscheme.NativeColorSchemeFunctions.SetPreference",
        "android_params": ["context"],
        "ios": "NativeColorSchemeFunctions.SetPreference"
    }
]
```

During a NativePHP mobile build, NativePHP:

1. Finds Composer packages with `"type": "nativephp-plugin"`.
2. Checks that the plugin service provider is listed in `NativeServiceProvider::plugins()`.
3. Reads `nativephp.json`.
4. Copies the Android Kotlin and iOS Swift source files into the generated native projects.
5. Generates bridge registration code so `NativeColorScheme.Get` and `NativeColorScheme.SetPreference` are callable from PHP.

On Android, the plugin reads:

```kotlin
context.resources.configuration.uiMode and Configuration.UI_MODE_NIGHT_MASK
```

It stores the user preference with `SharedPreferences`. On Android 12/API 31 and newer it also calls `UiModeManager#setApplicationNightMode(...)`, which lets Android persist app-local night mode and helps Android's native splash screen choose the matching `-night` resources on cold launch.

On Android 11/API 30 and older, the preference is still stored and returned by the plugin, but app-level splash/night-mode control is limited by the platform.

On iOS, the plugin reads:

```swift
traitCollection.userInterfaceStyle
```

It stores the user preference with `UserDefaults` and applies it to app windows with `overrideUserInterfaceStyle`.

The value comes from the native runtime configuration, not from Laravel, CSS, or browser state.

## Recommended App Flow

Most apps should expose three choices in settings:

- System
- Light
- Dark

Use the preference to render the selected option:

```php
$preference = NativeColorScheme::getPreference(); // system/light/dark
```

Use the effective scheme to render the UI:

```php
$scheme = NativeColorScheme::getColorScheme(); // light/dark
```

Example:

```php
if (NativeColorScheme::isSystem()) {
    // User selected "System".
}

if (NativeColorScheme::isDark()) {
    // Render dark UI. This may be because preference is dark,
    // or because preference is system and the OS is currently dark.
}
```

## Splash Screen Notes

Android can persist an app-local night mode through `UiModeManager#setApplicationNightMode(...)` on API 31+. This is the path that can affect Android's native splash resource selection.

iOS launch screens are shown before app code can fully run. The plugin applies the saved preference as early as NativePHP plugin initialization allows, but the first system launch screen may still follow the device appearance. For iOS, use a launch image that works acceptably in both modes, or show an in-app splash immediately after launch.

## Development Tip

During NativePHP development, setting your app version to `DEBUG` helps force bundle extraction while testing package changes:

```env
NATIVEPHP_APP_VERSION=DEBUG
```

If a native bridge change does not appear on device, rebuild the native project:

```bash
php artisan native:install --force
php artisan native:run
```

## Local Development

When developing the plugin beside a NativePHP app, use a Composer path repository:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../nativephp-color-scheme",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

Then require it:

```bash
composer require ruslanshigabutdinov/nativephp-color-scheme:@dev
```

PHP changes are picked up by Composer autoload. Native Kotlin, Swift, or manifest changes require rebuilding the native project:

```bash
php artisan native:run
```

For significant native or manifest changes:

```bash
php artisan native:install --force
```

## Validation

From a NativePHP app:

```bash
php artisan native:plugin:validate vendor/ruslanshigabutdinov/nativephp-color-scheme
php artisan native:plugin:list
```
