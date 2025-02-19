<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Services\CryptoService;

class CryptoController extends Controller
{
    protected $cryptoService;

    public function __construct(CryptoService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    public function index()
    {

        try {
            $cryptoData = $this->cryptoService->getCryptoData();

            $cryptoProcessedData = array_map(function ($crypto) {
                return [
                    'id' => $crypto['id'],
                    'name' => $crypto['name'],
                    'symbol' => $crypto['symbol'],
                    'price' => $crypto['quote']['USD']['price'],
                    'percent_change_1h' => $crypto['quote']['USD']['percent_change_1h'],
                    'percent_change_24h' => $crypto['quote']['USD']['percent_change_24h'],
                    'percent_change_7d' => $crypto['quote']['USD']['percent_change_7d'],
                    'percent_change_30d' => $crypto['quote']['USD']['percent_change_30d'],
                    'percent_change_90d' => $crypto['quote']['USD']['percent_change_90d'],
                    'volume_24h' => $crypto['quote']['USD']['volume_24h'],
                ];
            }, $cryptoData['data']);

            return view('dashboard.index', [
                'cryptoData' => $cryptoProcessedData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching cryptocurrency data: ' . $e->getMessage());
            return redirect()->route('dashboard')->withErrors('Failed to load cryptocurrency data.');
        }
    }

    public function getChartData($id)
    {
        try {

            Log::info('Fetching chart data for coin ID: ' . $id);

            $historicalData = $this->cryptoService->getHistoricalData($id);

            // dd($historicalData);
            $timestamps = [];
            $prices = [];
            foreach ($historicalData as $point) {
                $timestamps[] = date('H:i', strtotime($point['timestamp']));
                $prices[] = $point['quote']['USD']['price'];
            }

            return response()->json([
                'timestamps' => $timestamps,
                'prices' => $prices,
            ]);
        } catch (\Exception $e) {

            Log::error('Error fetching chart data: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
