# NativePHP Color Scheme

NativePHP Mobile plugin for reading the device OS color scheme from native Android and iOS code.

It returns:

- `light`
- `dark`
- `null` when the NativePHP bridge is unavailable

## Installation

Until this package is published on Packagist, install it from GitHub as a VCS repository.

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

Publish the NativePHP plugin provider if your app does not have one yet:

```bash
php artisan vendor:publish --tag=nativephp-plugins-provider
```

Register the plugin:

```bash
php artisan native:plugin:register ruslanshigabutdinov/nativephp-color-scheme
```

## Usage

```php
use RuslanShigabutdinov\NativeColorScheme\Facades\NativeColorScheme;

NativeColorScheme::get(); // 'light', 'dark', or null
NativeColorScheme::isDark();
NativeColorScheme::isLight();
```

You can also resolve the service directly:

```php
use RuslanShigabutdinov\NativeColorScheme\NativeColorScheme;

app(NativeColorScheme::class)->get();
```

## How It Works

The PHP API calls the NativePHP bridge method:

```php
nativephp_call('NativeColorScheme.Get', '{}');
```

The method is declared in `nativephp.json`:

```json
{
    "name": "NativeColorScheme.Get",
    "android": "com.ruslanshigabutdinov.nativecolorscheme.NativeColorSchemeFunctions.Get",
    "android_params": ["context"],
    "ios": "NativeColorSchemeFunctions.Get"
}
```

During a NativePHP mobile build, NativePHP:

1. Finds Composer packages with `"type": "nativephp-plugin"`.
2. Checks that the plugin service provider is listed in `NativeServiceProvider::plugins()`.
3. Reads `nativephp.json`.
4. Copies the Android Kotlin and iOS Swift source files into the generated native projects.
5. Generates bridge registration code so `NativeColorScheme.Get` is callable from PHP.

On Android, the plugin reads:

```kotlin
context.resources.configuration.uiMode and Configuration.UI_MODE_NIGHT_MASK
```

On iOS, the plugin reads:

```swift
traitCollection.userInterfaceStyle
```

The value comes from the native runtime configuration, not from Laravel, CSS, or browser state.

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
