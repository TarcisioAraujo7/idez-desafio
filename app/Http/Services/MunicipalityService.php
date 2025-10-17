<?php

namespace App\Http\Services;

use App\Exceptions\ProviderTemporarilyUnavailableException;
use App\Exceptions\UfNotFoundException;
use App\Http\Providers\BrasilApiProvider;
use App\Http\Providers\IbgeProvider;
use App\Http\Providers\Provider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class MunicipalityService
{
    private Provider $provider;

    private string $providerClassBaseName;

    public function __construct()
    {
        /** @var string $config */
        $config = config('services.municipality_provider', 'ibge');
        $configuredProvider = strtolower(trim($config));

        switch ($configuredProvider) {
            case 'brasilapi':
            case 'brasil-api':
                $this->provider = app(BrasilApiProvider::class);
                break;

            default:
                $this->provider = app(IbgeProvider::class);
                break;
        }
        $this->providerClassBaseName = class_basename($this->provider);
    }

    /**
     * Retorna os municípios da UF informada, buscando do cache ou do provedor configurado.
     *
     * @param  string  $uf  Sigla da unidade federativa (ex: "SE")
     * @return array{data: Collection<int, array{name: string, ibge_code: int}>, provider: string}|null
     *
     * @throws UfNotFoundException Quando a UF não é encontrada no provedor.
     * @throws ProviderTemporarilyUnavailableException Quando o provedor está temporariamente indisponível.
     */
    public function index(string $uf): ?array
    {
        $cacheKey = sprintf('municipalities_%s_%s', get_class($this->provider), strtoupper($uf));

        try {
            /** @var Collection<int, array{name: string, ibge_code: int}> $data */
            $data = Cache::remember($cacheKey, now()->addHours(6), function () use ($uf): Collection {
                return $this->provider->indexMunicipalities($uf);
            });

            return [
                'data' => $data,
                'provider' => $this->providerClassBaseName,
            ];
        } catch (UfNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Erro no provedor de municípios', [
                'uf' => $uf,
                'provider' => $this->providerClassBaseName,
                'exception' => $e->getMessage(),
            ]);

            throw new ProviderTemporarilyUnavailableException(
                "O provedor '".$this->providerClassBaseName."' não está disponível no momento, tente novamente mais tarde."
            );
        }
    }
}
