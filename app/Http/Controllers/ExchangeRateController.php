<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExchangeRate;
use GuzzleHttp\Client;

class ExchangeRateController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://www.cbr.ru',
            'timeout' => 5,
        ]);
    }

    public function fetchAndStoreExchangeRates()
    {
        try {
            $response = $this->client->request('GET', '/scripts/XML_daily.asp');
            $xmlContent = $response->getBody()->getContents();
            $rates = simplexml_load_string($xmlContent);
            
            foreach ($rates->Valute as $valute) {
                ExchangeRate::create([
                    'currency_code' => (string) $valute->CharCode,
                    'currency_name' => (string) $valute->Name,
                    'exchange_rate' => (float) str_replace(',', '.', $valute->Value),
                    'date' => date('Y-m-d'), // Current date
                ]);
            }

            return response()->json(['message' => 'Exchange rates stored successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch and store exchange rates'], 500);
        }
    }

    public function getExchangeRatesForPeriod(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        $exchangeRates = ExchangeRate::whereBetween('date', [$startDate, $endDate])->get();
        if (!$exchangeRates->isEmpty()) {
            // Data found, return response with data
            return response()->json([
                'message' => 'successfully',
                'data' => $exchangeRates,
                'error' => ''
            ], 200);
        } else {
            return response()->json([
                'data' => [],
                'error' => 'not_found'
            ], 404);
        }
    }
} 
        