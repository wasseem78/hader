<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch($localeOrSubdomain, $locale = null)
    {
        // If second parameter is null, it means we are on central domain and first param is locale
        // If second parameter is set, we are on tenant domain and first param is subdomain
        $targetLocale = $locale ?? $localeOrSubdomain;

        \Illuminate\Support\Facades\Log::info('LocaleController: Switching to ' . $targetLocale);
        
        if (in_array($targetLocale, ['en', 'ar'])) {
            Session::put('locale', $targetLocale);
            Session::save();
            App::setLocale($targetLocale);
        }
        return redirect()->back();
    }
}
