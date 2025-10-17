<?php

namespace App\Http\Providers;

use Illuminate\Support\Collection;
use App\Exceptions\UfNotFoundException;

interface Provider
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
    public function indexMunicipalities(string $uf): Collection;

    public function generateUrl(string $uf): string;
}
