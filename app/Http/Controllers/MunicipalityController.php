<?php

namespace App\Http\Controllers;

use App\Http\Services\MunicipalityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    public function __construct(private MunicipalityService $service) {}

    public function index(Request $request, string $uf): ?JsonResponse
    {
        $page = $request->query('page');
        $perPage = $request->query('per_page');

        $page = is_numeric($page) ? (int) $page : null;
        $perPage = is_numeric($perPage) ? (int) $perPage : null;

        $result = $this->service->index($uf, $page, $perPage);

        return response()->json(
            data: $result
        );
    }
}
