<?php

namespace App\Http\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use App\Exceptions\UfNotFoundException;

class BrasilApiProvider implements Provider
{
    public function indexMunicipalities(string $uf): Collection
    {
        $url = $this->generateUrl($uf);

        $response = Http::timeout(5)->get($url);

        if ($response->status() === 404) {
            throw new UfNotFoundException("UF '{$uf}' não encontrada em Brasil API.");
        }

        $response->throw();

        return collect($response->json())->map(fn ($item) => [
            'name' => $item['nome'],
            'ibge_code' => $item['codigo_ibge'],
        ]);
    }

    public function generateUrl(string $uf): string
    {
        return sprintf(
            'https://brasilapi.com.br/api/ibge/municipios/v1/%s',
            trim(strtoupper($uf))
        );
    }
}
