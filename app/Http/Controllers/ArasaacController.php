<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ArasaacController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q');
        if (!$query) return response()->json([]);

        $response = Http::get("https://api.arasaac.org/api/pictograms/pl/search/{$query}");

        return $response->json();
    }
}
