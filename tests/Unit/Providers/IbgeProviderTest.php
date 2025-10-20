<?php

namespace Tests\Unit\Providers;

use App\Exceptions\UfNotFoundException;
use App\Http\Providers\IbgeProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IbgeProviderTest extends TestCase
{
    public function test_ibge_provider_returns_municipalities_from_ibge_api(): void
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

    public function test_ibge_provider_throws_uf_not_found_exception_on_empty_response(): void
    {
        Http::fake([
            'https://servicodados.ibge.gov.br/*' => Http::response([], 200),
        ]);

        $provider = new IbgeProvider();

        $this->expectException(UfNotFoundException::class);
        $this->expectExceptionMessage("UF 'XX' não encontrada na API do IBGE.");

        $provider->indexMunicipalities('XX');
    }
}
