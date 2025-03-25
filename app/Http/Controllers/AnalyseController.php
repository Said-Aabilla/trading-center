<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Investment;
use App\Services\AnalyseService;
use Illuminate\Support\Facades\Auth;

class AnalyseController extends Controller
{
    protected AnalyseService $analyseService;

    public function __construct(AnalyseService $analyseService)
    {
        $this->analyseService = $analyseService;
    }

    /**
     * Retourne la répartition des actifs (actions/cryptos).
     */
    public function repartitionPortefeuille()
    {
        $userId = Auth::id();
        $breakdown = $this->analyseService->repartitionPortefeuille($userId);
        return response()->json($breakdown, 200);
    }

    /**
     * Calcule et retourne le gain encaissé pour un investissement donné.
     */
    public function gainEncaisse($invest_id)
    {
        $userId = Auth::id();
        $invest = Investment::where('user_id', $userId)->findOrFail($invest_id);
        $gainEncaisse = $this->analyseService->calculGainEncaisse($invest);
        return response()->json(['Gain encaissé' => $gainEncaisse], 200);
    }

    /** 
     * Calcule et retourne le gain non encaissé pour un investissement donné.
     */
    public function gainNonEncaisse($invest_id)
    {
        $userId = Auth::id();
        $invest = Investment::where('user_id', $userId)->findOrFail($invest_id);
        $gainNonEncaisse = $this->analyseService->calculGainNonEncaisse($invest);
        return response()->json(['Gain non encaissé' => $gainNonEncaisse], 200);
    }

    /**
     * Retourne l'analyse des performances par secteur (pour les actions).
     */
    public function performanceBySector()
    {
        $userId = Auth::id();
        $performance = $this->analyseService->analyseSectorPerformance($userId);
        return response()->json(['Analyse des performances par secteur' => $performance], 200);
    }

     /**
     * Calcule le ROI (non encaissé) pour un investissement donné en utilisant FIFO.
     */
    public function roiNonEncaisse($invest_id)
    {
        $userId = Auth::id();
        $invest = Investment::where('user_id', $userId)->findOrFail($invest_id);
        $roi = $this->analyseService->calculRoiNonEncaisse($invest);
        return response()->json(['ROI non encaissé' => number_format($roi, 2) . '%'], 200);
    }

    /**
     * Calcule le ROI (encaissé) pour un investissement donné en utilisant FIFO.
     */
    public function roiEncaisse($invest_id)
    {
        $userId = Auth::id();
        $invest = Investment::where('user_id', $userId)->findOrFail($invest_id);
        $roi = $this->analyseService->calculROIEncaisse($invest);
        return response()->json(['ROI encaissé' => number_format($roi, 2) . '%'], 200);
    }

    /**
     * Analyse la tendance d'un actif
     */
    public function analyseTendance(Request $request)
    {
        $symbol = $request->input('symbol');
        $prices = $this->analyseService->getHistoricalPrices($symbol);
        $trend = $this->analyseService->analyseTendance($prices);
        return response()->json(['Tendance de l\'actif ' . $symbol . ' : ' => $trend], 200);
    }
}
