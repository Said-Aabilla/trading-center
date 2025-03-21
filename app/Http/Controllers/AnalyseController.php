<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Investment;
use App\Services\AnalysisService;
use Illuminate\Support\Facades\Auth;

class AnalyseController extends Controller
{
    protected AnalysisService $analyseService;

    public function __construct(AnalysisService $analyseService)
    {
        $this->analyseService = $analyseService;
    }

    /**
     * Returns the asset distribution (stocks/cryptos).
     */
    public function portfolioDistribution()
    {
        $userId = Auth::id();
        $breakdown = $this->analyseService->portfolioDistribution($userId);
        return response()->json($breakdown, 200);
    }

    /**
     * Calculates and returns the realized gain for a given investment.
     */
    public function cashGain($invest_id)
    {
        $userId = Auth::id();
        $invest = Investment::where('user_id', $userId)->findOrFail($invest_id);
        $realizedGain = $this->analyseService->calculateRealizedGain($invest);
        return response()->json(['Realized Cash Gain' => $realizedGain], 200);
    }

    /** 
     * Calculates and returns the unrealized gain for a given investment.
     */
    public function nonCashGain($invest_id)
    {
        $userId = Auth::id();
        $invest = Investment::where('user_id', $userId)->findOrFail($invest_id);
        $unrealizedGain = $this->analyseService->calculateUnrealizedGain($invest);
        return response()->json(['Unrealized Gain' => $unrealizedGain], 200);
    }

    /**
     * Returns the performance analysis by sector (for stocks).
     */
    public function performanceBySector()
    {
        $userId = Auth::id();
        $performance = $this->analyseService->analyzeSectorPerformance($userId);
        return response()->json($performance, 200);
    }

    /**
     * Calculates the unrealized ROI for a given investment using FIFO.
     */
    public function unrealizedROI($invest_id)
    {
        $userId = Auth::id();
        $invest = Investment::where('user_id', $userId)->findOrFail($invest_id);
        $roi = $this->analyseService->calculateUnrealizedROI($invest);
        return response()->json(['Unrealized ROI' => $roi], 200);
    }

    /**
     * Calculates the realized ROI for a given investment.
     */
    public function realizedROI($invest_id)
    {
        $userId = Auth::id();
        $invest = Investment::where('user_id', $userId)->findOrFail($invest_id);
        $roi = $this->analyseService->calculateRealizedROI($invest);
        return response()->json(['Realized ROI' => $roi], 200);
    }

    /**
     * Analyzes the trend of an asset.
     */
    public function trendingAnalysis(Request $request)
    {
        $symbol = $request->input('symbol');
        $prices = $this->analyseService->getHistoricalPrices($symbol);
        $trend = $this->analyseService->analyzeTrend($prices);
        return response()->json(['Asset Trend for ' . $symbol . ' : ' => $trend], 200);
    }
}
