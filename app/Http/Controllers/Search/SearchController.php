<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Services\Search\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private SearchService $service)
    {
    }

    public function results(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        return response()->json(['results' => $this->service->search($request->user(), $query)]);
    }
}
