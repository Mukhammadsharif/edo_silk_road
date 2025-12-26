<?php

use App\Providers\ViewServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class,
    Spatie\TranslationLoader\TranslationServiceProvider::class,
    Spatie\TranslationLoader\TranslationServiceProvider::class,
    ViewServiceProvider::class,
];
