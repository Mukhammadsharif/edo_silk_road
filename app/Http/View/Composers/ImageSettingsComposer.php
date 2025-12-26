<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ImageSettingsComposer
{
    public function compose(View $view)
    {
        $settingsPath = storage_path('app/image_settings.json');
        $defaultSettings = [
            'background_image' => null,
            'logo_image' => null,
            'sent_doc_image' => null,
            'favicon' => null,
        ];

        if (!file_exists($settingsPath)) {
            file_put_contents($settingsPath, json_encode($defaultSettings));
        }

        $settings = json_decode(file_get_contents($settingsPath), true);
        $settings = is_array($settings) ? array_merge($defaultSettings, $settings) : $defaultSettings;

        $view->with([
            'backgroundImage' => $settings['background_image']
                ? Storage::url($settings['background_image'])
                : asset('images/banner_login_page.png'),

            'logoImage' => $settings['logo_image']
                ? Storage::url($settings['logo_image'])
                : asset('images/icon_login_page.png'),

            'sentDocImage' => $settings['sent_doc_image']
                ? Storage::url($settings['sent_doc_image'])
                : asset('images/letter_blank.png'),

            'favicon' => $settings['favicon']
                ? Storage::url($settings['favicon'])
                : asset('favicon.ico'),
        ]);
    }
}

