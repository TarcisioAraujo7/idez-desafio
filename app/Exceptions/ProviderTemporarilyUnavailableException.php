<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProviderTemporarilyUnavailableException extends Exception
{
    public function __construct(string $message = 'Serviço do provedor indisponível.', int $code = Response::HTTP_SERVICE_UNAVAILABLE)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode() ?: Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
