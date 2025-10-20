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
            case 'brasil_api':
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
     * É possível informar parâmetros opcionais de paginação (`page` e `perPage`);
     * se omitidos, o método retornará todos os municípios da UF sem paginação.
     *
     * @param  string  $uf  Sigla da unidade federativa (ex: "SE")
     * @param  int|null  $page  Número da página desejada. Se não informado, não aplica paginação.
     * @param  int|null  $perPage  Quantidade de registros por página. Se não informado, retorna todos os registros.
     * @return array{data: Collection<int, array{name: string, ibge_code: int}>, meta: array<string, mixed>, provider: string}|null
     *
     * @throws UfNotFoundException Quando a UF não é encontrada no provedor.
     * @throws ProviderTemporarilyUnavailableException Quando o provedor está temporariamente indisponível.
     */
    public function index(string $uf, ?int $page = null, ?int $perPage = null): ?array
    {
        $cacheKey = sprintf('municipalities_%s_%s', get_class($this->provider), strtoupper($uf));

        try {
            /** @var Collection<int, array{name: string, ibge_code: int}> $data */
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($uf): Collection {
                return $this->provider->indexMunicipalities($uf);
            });

            if (($page === null || $perPage === null)) {
                return [
                    'data' => $data,
                    'meta' => [
                        'total' => $data->count(),
                        'paginated' => false,
                    ],
                    'provider' => $this->providerClassBaseName,
                ];
            }

            $page = max($page, 1);
            $perPage = max($perPage, 1);

            $paginated = $data->forPage($page, $perPage)->values();

            return [
                'data' => $paginated,
                'meta' => [
                    'total' => $data->count(),
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => (int) ceil($data->count() / $perPage),
                    'paginated' => true,
                ],
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
