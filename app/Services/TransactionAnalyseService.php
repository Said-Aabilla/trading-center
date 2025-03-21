<?php

namespace App\Services;

use App\Models\Investment;

class TransactionAnalyseService
{
    

    protected AnalyseService $analyseService;

    public function __construct(AnalyseService $analyseService)
    {
        $this->analyseService = $analyseService;
    }

    /**
     * Calcule les plus-values totales pour l'utilisateur.
     */
    public function calculateTotalCapitalGains(int $userId): float
    {
        $investments = Investment::where('user_id', $userId)->get();
        $totalGain = 0;
        foreach ($investments as $investment) {
            $totalGain += $this->analyseService->calculGainEncaisse($investment);
        }
        return $totalGain;
    }

    /**
     * Génère un rapport fiscal simplifié.
     * Par exemple, applique une taxe forfaitaire de 15% sur les gains.
     */
    public function generateTaxReport(int $userId): array
    {
        $totalGain = $this->calculateTotalCapitalGains($userId);
        $tax = $totalGain > 0 ? $totalGain * 0.15 : 0;
        return [
            'Total of Capital Gains' => $totalGain,
            'Estimated Tax' => $tax,
        ];
    }
}
