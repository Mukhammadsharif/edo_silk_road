<?php

namespace App\Services;

use Spatie\TranslationLoader\LanguageLine;

class LanguageService
{
    //
    protected LanguageLine $line;
    public function __construct(LanguageLine $line)
    {
        //
        $this->line = $line;
    }

    public function getList()
    {
        //
        return LanguageLine::orderBy('group')->get();
    }
}
