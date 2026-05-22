# NativePHP Color Scheme

Read and control your NativePHP Mobile app's native appearance preference from Laravel.

The plugin supports the common theme flow:

- `system`
- `light`
- `dark`

When the preference is `system`, the effective color scheme follows the device appearance.

## Installation

```bash
composer require ruslanshigabutdinov/nativephp-color-scheme
```

Register the plugin:

```bash
php artisan native:plugin:register ruslanshigabutdinov/nativephp-color-scheme
```

If your app does not have `app/Providers/NativeServiceProvider.php` yet, publish it first:

```bash
php artisan vendor:publish --tag=nativephp-plugins-provider
```

## Usage

```php
use RuslanShigabutdinov\NativeColorScheme\Facades\NativeColorScheme;

NativeColorScheme::getPreference();  // system, light, dark, or null
NativeColorScheme::getColorScheme(); // effective light, dark, or null

NativeColorScheme::isSystem();
NativeColorScheme::isDark();
NativeColorScheme::isLight();
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

## Notes

Android is tested on device. iOS support is implemented, but still needs device/simulator validation.

On Android 12/API 31 and newer, the plugin applies the saved preference with native app night mode, which can help Android splash screens use the matching light or dark resources.

Because this package includes native Kotlin and Swift code, rebuild your NativePHP app after installing or updating it:

```bash
php artisan native:run
```

If native changes do not appear during development:

```bash
php artisan native:install --force
php artisan native:run
```
