<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

class TranslationController extends Controller
{
    public function getBatchTranslations(Request $request)
    {
        $codes = $request->input('codes', []);
        $lang  = $request->input('lang', config('app.locale'));

        if (empty($codes) || !is_array($codes)) {
            return response()->json(['error' => 'El parámetro codes debe ser un array no vacío'], 400);
        }

        if (count($codes) > 100) {
            return response()->json(['error' => 'Se permite un máximo de 100 códigos por request'], 400);
        }

        App::setLocale($lang);

        $translations = [];
        foreach ($codes as $code) {
            $translation = Lang::get($code);
            // Si no existe, devolver null (el cliente decide el fallback)
            $translations[$code] = ($translation === $code) ? null : $translation;
        }

        return response()->json([
            'lang'         => $lang,
            'translations' => $translations,
        ]);
    }

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
