<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use App\Http\View\Composers\ImageSettingsComposer;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', ImageSettingsComposer::class);
    }
}
