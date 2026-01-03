<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function getByLang($lang)
    {
        $translations = Translation::where('lang', $lang)->get();

        // grupowanie wg namespace
        $result = [];

        foreach ($translations as $t) {
            if (!isset($result[$t->namespace])) {
                $result[$t->namespace] = [];
            }

            $result[$t->namespace][$t->key] = $t->value;
        }

        return response()->json($result);
    }
}

