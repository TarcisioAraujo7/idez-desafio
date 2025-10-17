<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Services\MunicipalityService;

class MunicipalityController extends Controller
{
    public function __construct(private MunicipalityService $service)
    {
    }

    public function index(string $uf): ?JsonResponse
    {
        return response()->json(
            data: $this->service->index($uf)
        );
    }
}