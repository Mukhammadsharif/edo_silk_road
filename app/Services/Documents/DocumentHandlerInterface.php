<?php

namespace App\Services\Documents;

interface DocumentHandlerInterface
{
    public function handle($document, $journal, $request);
}
