<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Dedoc\Scramble\Attributes\Group;
use App\Http\Services\MunicipalityService;

#[Group(name: 'Municípios', weight: 0)]
class MunicipalityController extends Controller
{
    public function __construct(private MunicipalityService $service)
    {
    }

    /**
     * Lista municípios por UF.
     *
     * @response array{data: Collection<int, array{name: string, ibge_code: int}>, meta: array{total: int, paginated: boolean, per_page: int, current_page: int, last_page: int}, provider: string}|null
     */
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
