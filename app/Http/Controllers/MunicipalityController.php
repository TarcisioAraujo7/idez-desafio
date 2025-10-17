<?php

namespace App\Http\Controllers;

use App\Http\Services\MunicipalityService;
use Illuminate\Http\JsonResponse;

class MunicipalityController extends Controller
{
    public function __construct(private MunicipalityService $service) {}

    public function index(string $uf): ?JsonResponse
    {
        return response()->json(
            data: $this->service->index($uf)
        );
    }
}
