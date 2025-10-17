<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Http\Providers\IbgeProvider;
use Illuminate\Support\Facades\Http;
use App\Exceptions\UfNotFoundException;
use App\Http\Providers\BrasilApiProvider;

class BrasilApiProviderTest extends TestCase
{
    public function testBrasilApiProviderReturnsMunicipalitiesFromBrasilApi(): void
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

    public function testBrasilApiProviderThrowsUfNotFoundExceptionOn404(): void
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
