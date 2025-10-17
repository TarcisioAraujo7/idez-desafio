<?php

namespace Tests\Unit\Providers;

use App\Http\Providers\IbgeProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IbgeProviderTest extends TestCase
{
    public function testIbgeProviderReturnsMunicipalitiesFromIbgeApi(): void
    {
        Http::fake([
            'https://servicodados.ibge.gov.br/*' => Http::response([
                ['id' => 280001, 'nome' => 'Aracaju'],
                ['id' => 2806701, 'nome' => 'São Cristóvão'],
            ], 200),
        ]);

        $provider = new IbgeProvider();
        $result = $provider->indexMunicipalities('SE');

        $this->assertCount(2, $result);
        $this->assertEquals('Aracaju', $result[0]['name']);
        $this->assertEquals(280001, $result[0]['ibge_code']);
    }

}
