<?php

namespace App\Providers;

use App\Models\Document\DocumentJournals;
use App\Observers\DocumentJournalObserver;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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

        // Фиксация кадровых приказов для выгрузки в 1С
        DocumentJournals::observe(DocumentJournalObserver::class);

        Carbon::setLocale('ru');

        if (Request::getHost() == 'edo.eai.uz') {
            \URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS','on');
        }
        Blade::if('boardAdmin', function () {
            return auth()->check() && auth()->user()->canManageBoards();
        });
        View::composer(['layouts.sidebar', 'home'], function ($view) {
            // User Short Name
            $user_short_name = Auth::user()->username;

            $user_name = Auth::user()->userPersonal??0;

            if ($user_name) {
                $user_short_name = $user_name->shortName();
            }

            $menus = null;

            if (Auth::check()) {
                // Схлопываем повторы: один пункт меню приходит столько раз,
                // сколько у пользователя ролей, которые его открывают
                $menus = $this->dedupeSidebarMenus(Auth::user()->sidebarMenus());
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

    /**
     * Схлопнуть повторяющиеся пункты бокового меню.
     *
     * ПРИЧИНА ДУБЛЕЙ.
     * В role_menus у каждой роли своя строка на один и тот же пункт меню.
     * У пользователя с пятью ролями «Мои документы» приходило пятью записями:
     * menu_id у них одинаковый, а role_menus.id разные — поэтому unique('id')
     * в User::sidebarMenus() их не схлопывал, идентификаторы ведь и правда
     * различаются. Один и тот же пункт определяет menu_id, по нему и группируем.
     *
     * ПОЧЕМУ ЗДЕСЬ, А НЕ В МОДЕЛИ.
     * Дедупликация идёт ПОСЛЕ кэша (user_{id}_menus), поэтому:
     *   - кэш не нужно сбрасывать ни при установке, ни при откате;
     *   - выборка в User::sidebarMenus() остаётся нетронутой, а её вызывает
     *     ещё и HomeController;
     *   - откат — возврат одного этого файла.
     *
     * ПОЧЕМУ СЛИВАЕМ ДОЧЕРНИЕ.
     * Разные роли могут открывать разные подпункты одного раздела. Если взять
     * первую попавшуюся запись, пользователь потеряет доступ к подпунктам,
     * которые даёт вторая роль. Поэтому родителя берём первым по порядку,
     * а детей собираем из всех ролей — тоже без повторов.
     */
    private function dedupeSidebarMenus($menus)
    {
        if (!$menus || $menus->isEmpty()) {
            return $menus;
        }

        return $menus
            ->groupBy('menu_id')
            ->map(function ($sameMenu) {
                $parent = $sameMenu->first();

                // Пункт пришёл от одной роли — сливать нечего
                if ($sameMenu->count() === 1) {
                    return $parent;
                }

                $children = $sameMenu
                    ->pluck('child')
                    ->filter()
                    ->flatten()
                    ->unique('menu_id')
                    ->sortBy('order_number')
                    ->values();

                // Клонируем: setRelation на исходной модели изменил бы
                // коллекцию, лежащую в кэше, до конца запроса
                $parent = clone $parent;
                $parent->setRelation('child', $children);

                return $parent;
            })
            ->sortBy('order_number')
            ->values();
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
