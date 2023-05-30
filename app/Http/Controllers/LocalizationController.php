<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocalizationRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    public function setLocale(LocalizationRequest $request)
    {
        $locale = $request->locale;
        App::setLocale($locale);
        Session::put('locale', $locale);
        return response()->json($request->validated(), 200);
    }
}
