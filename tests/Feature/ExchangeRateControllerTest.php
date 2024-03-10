<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExchangeRateControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetExchangeRatesSuccess()
    {
        $response = $this->get('/exchange-rates');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data',
                     'error'
                 ]);
    }

    /**
     *
     * @return void
     */
    public function testGetExchangeRatesError()
    {
        $this->mockHttpClient('GET', 'https://www.cbr.ru/scripts/XML_daily.asp', 500);

        $response = $this->get('/exchange-rates');

        $response->assertStatus(500)
                 ->assertJsonStructure([
                     'error'
                 ]);
    }

    /**
     * Test retry mechanism when fetching exchange rates fails initially but succeeds after retries.
     *
     * @return void
     */
    public function testGetExchangeRatesRetry()
    {
        $this->mockHttpClient('GET', 'https://www.cbr.ru/scripts/XML_daily.asp', 500, 3);

        $response = $this->get('/exchange-rates');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data',
                     'error'
                 ]);
    }

    /**
     *
     * @param string $method
     * @param string $url
     * @param int $statusCode
     * @param int $retryAttempts
     * @return void
     */
    
    protected $mockGuzzleClient;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockGuzzleClient = $this->createMock(Client::class);
    }
    protected function mockHttpClient($method, $url, $statusCode, $retryAttempts = 0)
    {
        $this->mockGuzzleClient->shouldReceive('request')
            ->times($retryAttempts + 1)
            ->andReturn(response()->json([], $statusCode));
    }
}
