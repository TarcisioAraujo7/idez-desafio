<?php

namespace App\Http\Providers;

use App\Exceptions\UfNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class BrasilApiProvider implements Provider
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

        $response = Http::timeout(5)->retry(3, 100)->get($url);

        if ($response->status() === 404) {
            throw new UfNotFoundException("UF '{$uf}' não encontrada em Brasil API.");
        }

        $response->throw();

        /** @var array<int, array{nome: string, codigo_ibge: int}> $json */
        $json = $response->json();

        return collect($json)->map(fn ($item) => [
            'name' => (string) $item['nome'],
            'ibge_code' => (int) $item['codigo_ibge'],
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
