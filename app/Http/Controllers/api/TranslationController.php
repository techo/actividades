<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

class TranslationController extends Controller
{
    public function getTranslation(Request $request)
    {
        $code = $request->get('code');
        $lang = $request->get('lang', config('app.locale'));

        if (!$code) {
            return response()->json(['error' => 'Missing code parameter'], 400);
        }

        // Cambiar el idioma actual
        App::setLocale($lang);

        // Obtener la traducción
        $translation = Lang::get($code);

        // Si no existe, devolver el código original
        if ($translation === $code) {
            return response()->json([
                'success' => false,
                'message' => 'Translation not found',
                'code' => $code,
                'lang' => $lang
            ], 404);
        }

        return response()->json([
            'success' => true,
            'code' => $code,
            'lang' => $lang,
            'translation' => $translation
        ]);
    }
}
