<?php

namespace App\Http\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class BrasilApiProvider implements Provider
{
    public function indexMunicipalities(string $uf): Collection
    {
        $url = $this->generateUrl($uf);

        $response = Http::timeout(5)->get($url)->throw();

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
