<?php

namespace Tests\Unit\Providers;

use App\Http\Providers\BrasilApiProvider;
use App\Http\Providers\IbgeProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

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

}
