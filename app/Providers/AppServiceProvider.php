<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot()
    {
        Paginator::useBootstrap();

        Carbon::setLocale('ru');

        if (Request::getHost() == 'edo.eai.uz') {
            \URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS','on');
        }

        View::composer(['layouts.sidebar', 'home'], function ($view) {
            // User Short Name
            $user_short_name = Auth::user()->username;

            $user_name = Auth::user()->userPersonal??0;

            if ($user_name) {
                $user_short_name = $user_name->shortName();
            }

            $menus = null;

            if (Auth::check()) {
                $menus = Auth::user()->sidebarMenus();
            }

            // Protocols kategoriyasi uchun filtr
            $protocolMenus = $menus ? $menus->filter(function ($menu) {
                return isset($menu->menuList->code) && str_starts_with($menu->menuList->code, 'PRT_');
            }) : collect();

            // Oddiy parent menyular (Protocols'dan tashqari)
            $otherMenus = $menus ? $menus->reject(function ($menu) {
                return isset($menu->menuList->code) && str_starts_with($menu->menuList->code, 'PRT_');
            }) : collect();

            // Active menu
            $isActiveMenu = function ($url) {
                return request()->is(trim($url, '/')) ? 'active' : '';
            };

            $isParentOpen = function ($menu) {
                // Hozirgi urlning 1-segmenti
                $currentSegment = request()->segment(1);

                foreach ($menu->child as $child) {
                    $url_path = $child->menuList->url_path ?? '#';
                    $trimmedPath = trim($url_path, '/');

                    // Child path segmentini aniqlaymiz
                    $childSegment = Str::before($trimmedPath, '/');

                    if ($childSegment && $childSegment === $currentSegment) {
                        return 'open active';
                    }
                }

                return '';
            };

            $view->with([
                'userShortName' => $user_short_name,
                'sidebarMenus' => $otherMenus,
                'protocolMenus' => $protocolMenus,
                'isActiveMenu' => $isActiveMenu,
                'isParentOpen' => $isParentOpen,
            ]);

        });

        $this->app['view']->composer('*', function ($view) {
            $view->with('lang', App::getLocale());
        });
    }
    public function boot_old(): void
    {
        //
        Paginator::useBootstrap();

        Carbon::setLocale('ru');

        if (Request::getHost() == 'edo.eai.uz') {
            \URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS','on');
        }

        #\URL::forceScheme('https');

        View::composer('*', function ($view)
        {
            $sidebarMenu = [];

            if (Auth::check()) {

                $sidebarMenu = Auth::user()->getSidebarMenu();
            }

            $active_menu = '/'.request()->segment(1).'/'.request()->segment(2);
            $active_menu2 = '/'.request()->segment(1).'/'.request()->segment(2).'/'.request()->segment(3);

            $view->with(['sidebarMenu' => $sidebarMenu, 'active_menu' => $active_menu, 'active_menu2' => $active_menu2]);
        });

        $this->app['view']->composer('*', function ($view) {
            $view->with('lang', App::getLocale());
        });
    }
}
