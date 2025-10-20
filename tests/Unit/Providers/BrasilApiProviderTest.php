<?php

namespace Tests\Unit\Providers;

use App\Exceptions\UfNotFoundException;
use App\Http\Providers\BrasilApiProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BrasilApiProviderTest extends TestCase
{
    public function test_brasil_api_provider_returns_municipalities_from_brasil_api(): void
    {
        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/*' => Http::response([
                ['codigo_ibge' => 280001, 'nome' => 'Aracaju'],
                ['codigo_ibge' => 2806701, 'nome' => 'São Cristóvão'],
            ], 200),
        ]);

        $provider = new BrasilApiProvider();
        $result = $provider->indexMunicipalities('SE');

        $this->assertCount(2, $result);
        $this->assertEquals('Aracaju', $result[0]['name']);
        $this->assertEquals(280001, $result[0]['ibge_code']);
    }

    public function test_brasil_api_provider_throws_uf_not_found_exception_on404(): void
    {
        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/*' => Http::response([], 404),
        ]);

        $provider = new BrasilApiProvider();

        $this->expectException(UfNotFoundException::class);
        $this->expectExceptionMessage("UF 'XX' não encontrada em Brasil API.");

        $provider->indexMunicipalities('XX');
    }
}
