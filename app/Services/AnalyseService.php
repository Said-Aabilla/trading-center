<?php

namespace App\Services;

use App\Models\Investment;
use App\Services\YahooFinanceService;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Auth;
use Exception;

class AnalyseService
{
    protected $yahooFinanceService;
    protected $openAIService;

    public function __construct(YahooFinanceService $yahooFinanceService, OpenAIService $openAIService)
    {   
        $this->yahooFinanceService = $yahooFinanceService;
        $this->openAIService = $openAIService;
    }

       /**
     * Vérifie que l'investissement appartient bien à l'utilisateur connecté.
     * @throws Exception si l'investissement n'appartient pas à l'utilisateur.
     */
    private function checkOwnership(Investment $investment): void
    {
        if ($investment->user_id !== Auth::id()) {
            throw new Exception('Accès non autorisé : cet investissement ne vous appartient pas.');
        }
    }
     /**
     * Retourne la répartition en valeur du portefeuille entre actions et cryptos.
     *
     * @param int $userId
     * @return array
     */
    public function repartitionPortefeuille(int $userId): array
    {
        $investissements = Investment::where('user_id', $userId)->get();
        $repartition = ['action' => 0, 'crypto' => 0];

        foreach ($investissements as $investissement) {
            $valeur = $investissement->current_price * $investissement->quantity;
            if ($investissement->type === 'action') {
                $repartition['action'] += $valeur;
            } elseif ($investissement->type === 'crypto') {
                $repartition['crypto'] += $valeur;
            }
        }

        return $repartition;
    }

    /**
     * Calcule le gain encaissé pour un investissement en utilisant le coût moyen pondéré.
     * Formule : (prix de vente - cout moyen) * quantité vendue.
     *
     * @param Investment $investissement
     * @return float
     */
    public function calculGainEncaisse(Investment $investissement): float
    {
        $this->checkOwnership($investissement);
        $gainRealise = 0;
       $investissement->load(['transactions' => function($q) {
        $q->where('type', 'sell');
    }]);

    foreach ($investissement->transactions as $vente) {
        $gainRealise += ($vente->price - $investissement->cout_moyen) * $vente->quantity;
    }
    return $gainRealise;
    }

    /**
     * Calcule le gain non encaissé pour un investissement en utilisant le coût moyen pondéré.
     * Formule : (current_price - cout moyen) * quantité restante.
     *
     * @param Investment $investissement
     * @return float
     */
    public function calculGainNonEncaisse(Investment $investissement): float
    {
        $this->checkOwnership($investissement);
        return ($investissement->current_price - $investissement->cout_moyen) * $investissement->quantity;
    }

   /**
     * Calcule le ROI non encaissé pour un investissement en utilisant le coût moyen pondéré.
     * Formule : ((valeur actuelle - cout total) / cout total) * 100.
     *
     * @param Investment $investissement
     * @return float
     */
    public function calculRoiNonEncaisse(Investment $investissement): float
    {
    $this->checkOwnership($investissement);

    $quantiteRestante = $investissement->quantity;
    $coutMoyen = $investissement->cout_moyen; 

    if ($quantiteRestante <= 0 || $coutMoyen <= 0) {
        return 0;
    }   

    $coutTotal = $coutMoyen * $quantiteRestante;
    $valeurActuelle = $investissement->current_price * $quantiteRestante;

    if ($coutTotal == 0) {
        return 0;
    }

    return (($valeurActuelle - $coutTotal) / $coutTotal) * 100;
    }

    /**
     * Calcule le ROI encaissé pour un investissement en utilisant le coût moyen pondéré.
     * Formule : (gain total / (cout moyen * quantité vendue)) * 100.
     *
     * @param Investment $investissement
     * @return float
     */
    public function calculROIEncaisse(Investment $investissement): float
    {
        $this->checkOwnership($investissement);
        $investissement->load(['transactions' => function($q) {
            $q->where('type', 'sell');
        }]);
        $gainTotal = 0;
        $coutTotal = 0;
        foreach ($investissement->transactions as $vente) {
            $gain      = ($vente->price - $investissement->cout_moyen) * $vente->quantity;
            $cout      = $investissement->cout_moyen * $vente->quantity;
            $gainTotal += $gain;
            $coutTotal += $cout;
        }
        if ($coutTotal == 0) {  
            return 0;
        }
        return ($gainTotal / $coutTotal) * 100;
    }

   /**
     * Analyse les performances par secteur pour les actions.
     * Pour chaque investissement, la valeur initiale est calculée comme : cout moyen * quantité,
     * et la valeur actuelle comme : current_price * quantité.
     *
     * @param int $userId
     * @return array
     */
    public function analyseSectorPerformance(int $userId): array
    {
        $investissements = Investment::where('user_id', $userId)
            ->where('type', 'action')
            ->whereNotNull('sector')
            ->get();

        $secteurs = [];
        foreach ($investissements as $investissement) {
            $secteur = $investissement->sector;
            $valeurInitiale = $investissement->cout_moyen * $investissement->quantity;
            $valeurActuelle = $investissement->current_price * $investissement->quantity;
            if (!isset($secteurs[$secteur])) {
                $secteurs[$secteur] = [
                    'total_initial' => 0,
                    'total_current' => 0,
                ];
            }
            $secteurs[$secteur]['total_initial'] += $valeurInitiale;
            $secteurs[$secteur]['total_current'] += $valeurActuelle;
        }

        $resultats = [];
        foreach ($secteurs as $secteur => $donnees) {
            $roi = $donnees['total_initial'] > 0 ?
                (($donnees['total_current'] - $donnees['total_initial']) / $donnees['total_initial']) * 100
                : 0;
            $resultats[$secteur] = [
                'roi' => $roi,
                'total_initial' => $donnees['total_initial'],
                'total_current' => $donnees['total_current'],
            ];
        }
        return $resultats;
    }

    

    /**
     * Récupère l'historique des prix d'un actif via Yahoo Finance
     *
     * @param string $symbol
     * @return array
     */
    public function getHistoricalPrices(string $symbol): array
    {
        return $this->yahooFinanceService->getHistoricalPrices($symbol);
    }

   /**
     * Analyse la tendance d'un actif via OpenAI en utilisant l'historique des prix
     *
     * @param array $prices
     * @return string
     */
    public function analyseTendance(array $prices): string
    {
        if (empty($prices)) {
            return "Aucun prix disponible";
        }

        $formattedPrices = "";
        foreach ($prices as $p) {
            $formattedPrices .= date('Y-m-d', $p[0]) . " : " . $p[1] . "\n";
        }

        // Construction du prompt
        $prompt = "Voici l'historique des prix d'un actif sur les 30 derniers jours :\n"
            . $formattedPrices
            . "\nAnalyse la tendance globale de cet actif."
            . " Si la tendance est haussière (montante), réponds 'Bullish (Tendance haussière)'. "
            . " Si elle est baissière (descendante), réponds 'Bearish (Tendance baissière)'. "
            . "N'ajoute aucune autre explication.";

        return $this->openAIService->analyseTendance($prompt);
    }

}
