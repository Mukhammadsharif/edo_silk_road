<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EimzoTestController extends Controller
{
    /**
     * Display the E-IMZO test page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('e-imzo.test');
    }

    public function privacy()
    {
        return view('e-imzo.privacy');
    }
}
