<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocalizationRequest;
use Illuminate\Support\Facades\App;

class LocalizationController extends Controller
{
    public function setLocale(LocalizationRequest $request)
    {
        $locale = $request->locale;
        App::setLocale($locale);
        session()->put('locale', $locale);
        return response()->json($request->validated(), 201);
    }
}
