<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Services\MunicipalityService;

class MunicipalityController extends Controller
{
    public function __construct(private MunicipalityService $service) {}

    public function index(Request $request, string $uf): ?JsonResponse
    {
        $page = $request->query('page');
        $perPage = $request->query('per_page');

        $result = $this->service->index($uf, $page, $perPage);
        return response()->json(
            data: $result
        );
    }
}
