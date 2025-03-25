<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TransactionAnalyseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class TransactionAnalyseController extends Controller
{
    protected TransactionAnalyseService $transactionAnalyseService;

    public function __construct(TransactionAnalyseService $transactionAnalyseService)
    {
        $this->transactionAnalyseService = $transactionAnalyseService;
    }

    

    /**
     * Calcule la somme des plus-values pour l'utilisateur.
     */
    public function totalCapitalGains()
    {
        $userId = Auth::id();
        $totalGains = $this->transactionAnalyseService->calculateTotalCapitalGains($userId);
        return response()->json(['Total des plus-values' => $totalGains], 200);
    }

    /**
     * Génère un rapport fiscal simplifié pour l'utilisateur.
     */
    public function generateTaxReport()
    {
        $userId = Auth::id();
        $report = $this->transactionAnalyseService->generateTaxReport($userId);
        return response()->json([
            'message' => 'Rapport fiscal généré avec succès.',
            'data' => $report
        ], 200);
    }
}
