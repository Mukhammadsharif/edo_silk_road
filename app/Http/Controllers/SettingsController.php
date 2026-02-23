<?php

namespace App\Http\Controllers;

use App\Helpers\Settings;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class SettingsController extends Controller
{
    //
    public function index()
    {
        // Assume isDemo is defined elsewhere (e.g., config or middleware)
        $isDemo = Settings::get('is_demo', false); // Adjust based on your setup

        $telegramBotToken = DotenvEditor::getValue('TELEGRAM_BOT_TOKEN');
        $telegramWebHookUrl = DotenvEditor::getValue('TELEGRAM_WEBHOOK_URL');
        $appUrl = DotenvEditor::getValue('APP_URL');

        $roles = $this->authActiveUserRoleNames();

        return view('admin.settings.index', compact('isDemo',
            'telegramBotToken', 'telegramWebHookUrl', 'appUrl', 'roles'));
    }

    public function logFile($key)
    {
        //
        if ($key === 'log') {
            $pathToFile = storage_path() . "/logs/laravel.log";

            return response()->file($pathToFile);

        } elseif ($key === 'routes') {
            Artisan::call('route:list');
            $message = Artisan::output();

            return response()->json($message);
        }

        return 0;
    }

    public function cacheClear($type)
    {
        //
        if ($type == 'route')
        {

            Artisan::call('route:clear');
            $message = 'Route cleared';

        }
        elseif ($type == 'config')
        {

            Artisan::call('config:clear');
            $message = 'Config cleared';

        }
        elseif ($type == 'clear')
        {

            Artisan::call('cache:clear');
            $message = 'Cache cleared';

        }
        elseif ($type == 'view')
        {

            Artisan::call('view:cache');
            $message = 'View cleared';

        }
        elseif ($type == 'routes')
        {

            Artisan::call('route:list');
            $message = Artisan::output();

        } else {

            $message = 'not found';

        }

        return back()->with('success', $message);
    }

    public function updateDemoSetting(Request $request)
    {
        $isDemo = $request->has('is_demo');

        Settings::set('is_demo', $isDemo);

        return redirect()->back()->with('success', 'Demo setting updated.');
    }

    public function imageUpload(Request $request)
    {
        $request->validate([
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'logo_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'sent_doc_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon' => 'nullable|file|mimes:ico,png|max:2048',
        ]);

        // Load current settings
        $settingsPath = 'image_settings.json';
        $settings = Storage::disk('local')->exists($settingsPath)
            ? json_decode(Storage::disk('local')->get($settingsPath), true)
            : ['background_image' => null, 'logo_image' => null, 'sent_doc_image' => null, 'favicon' => null];

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            $backgroundImage = $request->file('background_image');
            $path = $backgroundImage->store('images', 'public');
            $settings['background_image'] = $path;
        }

        // Handle logo image upload
        if ($request->hasFile('logo_image')) {
            $logoImage = $request->file('logo_image');
            $path = $logoImage->store('images', 'public');
            $settings['logo_image'] = $path;
        }

        // Handle sent doc image upload
        if ($request->hasFile('sent_doc_image')) {
            $logoImage = $request->file('sent_doc_image');
            $path = $logoImage->store('images', 'public');
            $settings['sent_doc_image'] = $path;
        }

        if ($request->hasFile('favicon')) {
            $faviconImage = $request->file('favicon');
            $path = $faviconImage->store('images', 'public');
            $settings['favicon'] = $path;
        }

        // Save updated settings
        Storage::disk('local')->put($settingsPath, json_encode($settings));

        return redirect()->back()->with('success', 'Images updated successfully!');
    }

    public function updateEnv(Request $request)
    {
        DotenvEditor::setKeys([
            'APP_URL' => $request->input('app_url'),
            'TELEGRAM_BOT_TOKEN' => $request->input('bot_token'),
            'TELEGRAM_WEBHOOK_URL' => $request->input('webhook_url'),
        ]);

        DotenvEditor::save();

        return redirect()->back()->with('success', 'Env updated successfully!');
    }
}
