<?php

namespace App\Services;

use App\Models\Investment;
use Scheb\YahooFinanceApi\ApiClientFactory;
use Exception;

class PriceSyncService
{
    /**
     * Synchronise les prix pour tous les investissements d'un utilisateur donné.
     *
     * @param int $userId
     * @return bool
     */
    public function syncPricesForInvestments(int $userId): bool
    {
        $investments = Investment::where('user_id', $userId)->get();

        foreach ($investments as $investment) {
            try {
                    
                    $client = ApiClientFactory::createApiClient();
                    $quote = $client->getQuote($investment->symbol);
                    if ($quote->getRegularMarketPrice()) {
                        $currentPrice = $quote->getRegularMarketPrice();
                        $investment->current_price = $currentPrice;
                        $investment->save();
                
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return true;
    }
}
