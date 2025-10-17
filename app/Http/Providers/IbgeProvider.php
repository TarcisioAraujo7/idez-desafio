<?php

namespace App\Http\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class IbgeProvider implements Provider
{
    public function indexMunicipalities(string $uf): Collection
    {
        $url = $this->generateUrl($uf);

        $response = Http::timeout(5)->get($url)->throw();

        return collect($response->json())->map(fn ($item) => [
            'name' => $item['nome'],
            'ibge_code' => $item['id'],
        ]);
    }

    public function generateUrl(string $uf): string
    {
        return sprintf(
                'https://servicodados.ibge.gov.br/api/v1/localidades/estados/%s/municipios',
                trim(strtolower($uf))
            );
    }
}