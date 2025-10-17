<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Debug\ShouldntReport;

class UfNotFoundException extends Exception implements ShouldntReport
{
    public function __construct(string $message = 'Unidade federativa não encontrada no provedor.')
    {
        parent::__construct($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Renderiza o erro como uma resposta HTTP em JSON.
     */
    public function render(Request $request): JsonResponse
    {
        $responseData = ['message' => $this->getMessage()];

        return response()->json($responseData, Response::HTTP_NOT_FOUND);
    }
}
