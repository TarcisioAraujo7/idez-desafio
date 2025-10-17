<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MunicipalityTest extends TestCase
{
    public function test_municipalities_can_be_listed_by_uf(): void
    {
        $municipalitiesArray = [
            ['id' => 280001, 'nome' => 'Aracaju'],
            ['id' => 2806008, 'nome' => 'Ribeirópolis'],
            ['id' => 2806701, 'nome' => 'São Cristóvão'],
        ];

        Http::fake([
            'https://servicodados.ibge.gov.br/*' => Http::response($municipalitiesArray, 200),
        ]);

        $response = $this->call(
            method: 'GET',
            uri: route('municipalities.index', ['uf' => 'SE'])
        );

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson([
            'provider' => 'IbgeProvider',
            'data' => [
                ['name' => 'Aracaju', 'ibge_code' => 280001],
                ['name' => 'Ribeirópolis', 'ibge_code' => 2806008],
                ['name' => 'São Cristóvão', 'ibge_code' => 2806701],
            ],
        ]);
    }

    public function test_not_found_response_should_be_returned_when_invalid_uf_is_passed(): void
    {
        Config::set('services.municipality_provider', 'brasil-api');

        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/*' => Http::response([], 404),
        ]);

        $response = $this->call(
            method: 'GET',
            uri: route('municipalities.index', ['uf' => 'XX'])
        );

        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $response->assertJson(['message' => "UF 'XX' não encontrada em Brasil API."]);
    }

    public function test_service_unavailable_response_should_be_returned_when_provider_fails(): void
    {
        Config::set('services.municipality_provider', 'brasil-api');

        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/*' => Http::response([], 500),
        ]);

        $response = $this->call(
            method: 'GET',
            uri: route('municipalities.index', ['uf' => 'XX'])
        );

        $response->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);

        $response->assertJson(['message' => "O provedor 'BrasilApiProvider' não está disponível no momento, tente novamente mais tarde."]);
    }
}
