<?php

namespace Tests\Unit\Services;

use App\Exceptions\ProviderTemporarilyUnavailableException;
use App\Exceptions\UfNotFoundException;
use App\Http\Providers\BrasilApiProvider;
use App\Http\Services\MunicipalityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MunicipalityServiceTest extends TestCase
{
    public function test_municipality_service_should_return_cached_response_if_available(): void
    {
        $municipalitiesCollection = collect(['name' => 'Ribeirópolis', 'ibge_code' => 2806008]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($municipalitiesCollection);

        $expectedResponse = [
            'data' => $municipalitiesCollection,
            'provider' => 'IbgeProvider',
        ];

        $service = new MunicipalityService;

        $response = $service->index('SE');

        $this->assertSame($expectedResponse, $response);
    }

    public function test_municipality_service_fetches_data_from_provider_and_caches(): void
    {
        Config::set('services.municipality_provider', 'brasil-api');

        $municipalitiesCollection = collect(['name' => 'Ribeirópolis', 'ibge_code' => 2806008]);

        $mockProvider = $this->mock(BrasilApiProvider::class, function ($mock) use (&$municipalitiesCollection) {
            $mock->shouldReceive('indexMunicipalities')
                ->once()
                ->with('SE')
                ->andReturn($municipalitiesCollection);
        });

        $this->app->instance(BrasilApiProvider::class, $mockProvider);

        $expectedResponse = [
            'data' => $municipalitiesCollection,
            'provider' => get_class($mockProvider),
        ];
        $cacheKey = sprintf('municipalities_%s_SE', get_class($mockProvider));

        $service = new MunicipalityService;

        $response = $service->index('SE');

        $this->assertTrue(Cache::has($cacheKey), 'O cache da chave esperada não foi criado.');
        $this->assertSame($expectedResponse, $response);
    }

    public function test_municipality_service_throws_uf_not_found_exception_when_invalid_uf_is_passed(): void
    {
        Config::set('services.municipality_provider', 'brasil-api');

        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/*' => Http::response([], 404),
        ]);

        $service = new MunicipalityService;

        $this->expectException(UfNotFoundException::class);
        $this->expectExceptionMessage("UF 'XX' não encontrada em Brasil API.");

        $service->index('XX');
    }

    public function test_municipality_service_throws_provider_unavailable_exception_when_provider_returns_error(): void
    {
        Config::set('services.municipality_provider', 'brasil-api');

        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/*' => Http::response([], 500),
        ]);

        $service = new MunicipalityService;

        $this->expectException(ProviderTemporarilyUnavailableException::class);
        $this->expectExceptionMessage("O provedor 'BrasilApiProvider' não está disponível no momento, tente novamente mais tarde.");

        $service->index('SE');
    }
}
