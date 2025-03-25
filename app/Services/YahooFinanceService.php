<?php
namespace App\Services;

use Scheb\YahooFinanceApi\ApiClientFactory;
use Exception;

class YahooFinanceService
{
    /**
     * Récupère l'historique des prix via l'API YahooFinance
     * sur les 30 derniers jours (intervalle quotidien).
     *
     * @param string $symbol
     * @return array Tableau [timestamp, prix_de_fermeture]
     */
    public function getHistoricalPrices(string $symbol): array
    {
        try {
            $client = ApiClientFactory::createApiClient();
            $historicalData = $client->getHistoricalQuoteData(
                $symbol,
                \Scheb\YahooFinanceApi\ApiClient::INTERVAL_1_DAY,
                new \DateTime('-30 days'),
                new \DateTime()
            );

            $prices = [];
            foreach ($historicalData as $day) {
                $prices[] = [
                    strtotime($day->getDate()->format('Y-m-d')),
                    $day->getClose()
                ];
            }
            return $prices;
        } catch (Exception $e) {
            // En production, vous pouvez logger l'erreur
            return [];
        }
    }
}
