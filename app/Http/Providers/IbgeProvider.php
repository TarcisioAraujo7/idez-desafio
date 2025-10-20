<?php

namespace App\Http\Providers;

use App\Exceptions\UfNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class IbgeProvider implements Provider
{
    /**
     * Retorna a lista de municípios da UF informada.
     *
     * @param  string  $uf  Sigla da unidade federativa (ex: "SE")
     * @return Collection<int, array{name: string, ibge_code: int}>
     *
     * @throws UfNotFoundException Quando a UF não é encontrada no provedor.
     * @throws \Illuminate\Http\Client\RequestException Em caso de falha HTTP.
     */
    public function indexMunicipalities(string $uf): Collection
    {
        $url = $this->generateUrl($uf);

        $response = Http::timeout(5)->retry(3, 100)->get($url)->throw();

        if (empty($response->json())) {
            throw new UfNotFoundException("UF '{$uf}' não encontrada na API do IBGE.");
        }

        /** @var array<int, array{nome: string, id: int}> $json */
        $json = $response->json();

        return collect($json)->map(fn ($item) => [
            'name' => (string) $item['nome'],
            'ibge_code' => (int) $item['id'],
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
