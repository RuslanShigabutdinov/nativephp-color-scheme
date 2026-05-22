<?php

namespace RuslanShigabutdinov\NativeColorScheme;

use Illuminate\Support\ServiceProvider;

class NativeColorSchemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NativeColorScheme::class);
    }
}
