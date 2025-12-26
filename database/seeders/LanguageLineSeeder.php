<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Spatie\TranslationLoader\LanguageLine;

class LanguageLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        LanguageLine::truncate();

        // Path to your JSON file
        $jsonFile = base_path('database/seeders/data/language_lines_new.json');

        // Read and decode the JSON file
        $jsonData = json_decode(File::get($jsonFile), true);

        foreach ($jsonData['language_lines'] as $role) {
            $text = json_decode($role['text'], true);
            LanguageLine::create([
                'id' => $role['id'],
                'group' => $role['group'],
                'key' => $role['key'],
                'text' => [
                    'uz' => $text['uz'],
                    'ru' => $text['ru'],
                    'en' => $text['en'],
                ]
            ]);
        }
    }
}
