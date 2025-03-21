<?php

namespace App\Services;

use App\Models\Investment;
use Exception;
use Illuminate\Support\Facades\Http;
use Scheb\YahooFinanceApi\ApiClient;
use Scheb\YahooFinanceApi\ApiClientFactory;

class AnalysisService
{
    /**
     * Returns the portfolio distribution in terms of value between stocks and cryptocurrencies.
     */
    public function portfolioDistribution(int $userId): array
    {
        $investments = Investment::where('user_id', $userId)->get();
        $distribution = ['action' => 0, 'crypto' => 0];

        foreach ($investments as $investment) {
            $value = $investment->current_price * $investment->quantity;
            if ($investment->type === 'action') {
                $distribution['action'] += $value;
            } elseif ($investment->type === 'crypto') {
                $distribution['crypto'] += $value;
            }
        }
        return $distribution;
    }

    /**
     * Calculates the realized gain for an investment using the FIFO method.
     *
     * @param Investment $investment
     * @return float
     */
    public function calculateRealizedGain(Investment $investment): float
    {
        $buyTransactions = $investment->transactions()
            ->where('type', 'buy')
            ->orderBy('transaction_date', 'asc')
            ->get();

        $sellTransactions = $investment->transactions()
            ->where('type', 'sell')
            ->orderBy('transaction_date', 'asc')
            ->get();

        $realizedGain = 0;
        $buyQueue = [];

        foreach ($buyTransactions as $buy) {
            $buyQueue[] = [
                'quantity' => $buy->quantity,
                'price'    => $buy->price,
            ];
        }

        foreach ($sellTransactions as $sell) {
            $sellQuantity = $sell->quantity;
            $sellPrice = $sell->price;

            while ($sellQuantity > 0 && !empty($buyQueue)) {
                $oldestBuy = array_shift($buyQueue);
                if ($oldestBuy['quantity'] <= $sellQuantity) {
                    $realizedGain += $oldestBuy['quantity'] * ($sellPrice - $oldestBuy['price']);
                    $sellQuantity -= $oldestBuy['quantity'];
                } else {
                    $realizedGain += $sellQuantity * ($sellPrice - $oldestBuy['price']);
                    $oldestBuy['quantity'] -= $sellQuantity;
                    $sellQuantity = 0;
                    array_unshift($buyQueue, $oldestBuy);
                }
            }
        }

        return $realizedGain;
    }

    /**
     * Calculates the unrealized gain (or loss) for an investment using the FIFO method.
     *
     * @param Investment $investment
     * @return float
     */
    public function calculateUnrealizedGain(Investment $investment): float
    {
        $buyTransactions = $investment->transactions()
            ->where('type', 'buy')
            ->orderBy('transaction_date', 'asc')
            ->get();

        $buyQueue = [];
        foreach ($buyTransactions as $buy) {
            $buyQueue[] = [
                'quantity' => $buy->quantity,
                'price'    => $buy->price,
            ];
        }

        $sellTransactions = $investment->transactions()
            ->where('type', 'sell')
            ->orderBy('transaction_date', 'asc')
            ->get();

        foreach ($sellTransactions as $sell) {
            $sellQuantity = $sell->quantity;
            while ($sellQuantity > 0 && !empty($buyQueue)) {
                $lot = array_shift($buyQueue);
                if ($lot['quantity'] <= $sellQuantity) {
                    $sellQuantity -= $lot['quantity'];
                } else {
                    $lot['quantity'] -= $sellQuantity;
                    $sellQuantity = 0;
                    array_unshift($buyQueue, $lot);
                }
            }
        }

        $unrealizedGain = 0;
        foreach ($buyQueue as $lot) {
            $unrealizedGain += ($investment->current_price - $lot['price']) * $lot['quantity'];
        }

        return $unrealizedGain;
    }

    /**
     * Calculates the unrealized ROI (Return on Investment) for an investment using the FIFO method.
     *
     * @param Investment $investment
     * @return float
     */
    public function calculateUnrealizedROI(Investment $investment): float
    {
        $buyTransactions = $investment->transactions()
            ->where('type', 'buy')
            ->orderBy('transaction_date', 'asc')
            ->get();

        $buyQueue = [];
        foreach ($buyTransactions as $buy) {
            $buyQueue[] = [
                'quantity' => $buy->quantity,
                'price'    => $buy->price,
            ];
        }

        $sellTransactions = $investment->transactions()
            ->where('type', 'sell')
            ->orderBy('transaction_date', 'asc')
            ->get();

        foreach ($sellTransactions as $sell) {
            $sellQuantity = $sell->quantity;
            while ($sellQuantity > 0 && !empty($buyQueue)) {
                $lot = array_shift($buyQueue);
                if ($lot['quantity'] <= $sellQuantity) {
                    $sellQuantity -= $lot['quantity'];
                } else {
                    $lot['quantity'] -= $sellQuantity;
                    $sellQuantity = 0;
                    array_unshift($buyQueue, $lot);
                }
            }
        }

        $costBasis = 0;
        $remainingQuantity = 0;
        foreach ($buyQueue as $lot) {
            $costBasis += $lot['quantity'] * $lot['price'];
            $remainingQuantity += $lot['quantity'];
        }

        if ($costBasis == 0) {
            return 0;
        }

        $currentValue = $investment->current_price * $remainingQuantity;

        return (($currentValue - $costBasis) / $costBasis) * 100;
    }

    /**
     * Calculates the realized ROI for an investment using the FIFO method.
     *
     * @param Investment $investment
     * @return float
     */
    public function calculateRealizedROI(Investment $investment): float
    {
        $buyTransactions = $investment->transactions()
            ->where('type', 'buy')
            ->orderBy('transaction_date', 'asc')
            ->get();

        $buyQueue = [];
        foreach ($buyTransactions as $buy) {
            $buyQueue[] = [
                'quantity' => $buy->quantity,
                'price'    => $buy->price,
            ];
        }

        $sellTransactions = $investment->transactions()
            ->where('type', 'sell')
            ->orderBy('transaction_date', 'asc')
            ->get();

        $totalRealizedGain = 0;
        $totalCostBasis = 0;

        foreach ($sellTransactions as $sell) {
            $sellQuantity = $sell->quantity;
            $sellPrice = $sell->price;

            while ($sellQuantity > 0 && !empty($buyQueue)) {
                $lot = array_shift($buyQueue);

                if ($lot['quantity'] <= $sellQuantity) {
                    $costBasisForLot = $lot['quantity'] * $lot['price'];
                    $realizedGain = $lot['quantity'] * ($sellPrice - $lot['price']);
                    $totalCostBasis += $costBasisForLot;
                    $totalRealizedGain += $realizedGain;
                    $sellQuantity -= $lot['quantity'];
                } else {
                    $costBasisForLot = $sellQuantity * $lot['price'];
                    $realizedGain = $sellQuantity * ($sellPrice - $lot['price']);
                    $totalCostBasis += $costBasisForLot;
                    $totalRealizedGain += $realizedGain;
                    $lot['quantity'] -= $sellQuantity;
                    $sellQuantity = 0;
                    array_unshift($buyQueue, $lot);
                }
            }
        }

        if ($totalCostBasis == 0) {
            return 0;
        }

        return ($totalRealizedGain / $totalCostBasis) * 100;
    }

    /**
     * Analyzes the sector performance for stocks.
     *
     * @param int $userId
     * @return array
     */
    public function analyzeSectorPerformance(int $userId): array
    {
        $investments = Investment::where('user_id', $userId)
            ->where('type', 'action')
            ->whereNotNull('sector')
            ->get();

        $sectors = [];
        foreach ($investments as $investment) {
            $sector = $investment->sector;

            // Calculate cost basis of units currently held (FIFO)
            $costBasis = $this->getCostBasis($investment);

            // Current value = current_price * quantity held
            $currentValue = $investment->current_price * $investment->quantity;

            if (!isset($sectors[$sector])) {
                $sectors[$sector] = [
                    'total_initial' => 0,
                    'total_current' => 0,
                ];
            }
            $sectors[$sector]['total_initial'] += $costBasis;
            $sectors[$sector]['total_current'] += $currentValue;
        }

        $sectorPerformance = [];
        foreach ($sectors as $sector => $data) {
            $performance = (($data['total_current'] - $data['total_initial']) / $data['total_initial']) * 100;
            $sectorPerformance[$sector] = $performance;
        }

        return $sectorPerformance;
    }

    /**
     * Helper function to calculate cost basis using FIFO method for stocks.
     *
     * @param Investment $investment
     * @return float
     */
    private function getCostBasis(Investment $investment): float
    {
        $buyTransactions = $investment->transactions()
            ->where('type', 'buy')
            ->orderBy('transaction_date', 'asc')
            ->get();

        $buyQueue = [];
        foreach ($buyTransactions as $buy) {
            $buyQueue[] = [
                'quantity' => $buy->quantity,
                'price'    => $buy->price,
            ];
        }

        $costBasis = 0;
        $remainingQuantity = $investment->quantity;
        while ($remainingQuantity > 0 && !empty($buyQueue)) {
            $lot = array_shift($buyQueue);

            if ($lot['quantity'] <= $remainingQuantity) {
                $costBasis += $lot['quantity'] * $lot['price'];
                $remainingQuantity -= $lot['quantity'];
            } else {
                $costBasis += $remainingQuantity * $lot['price'];
                $lot['quantity'] -= $remainingQuantity;
                $remainingQuantity = 0;
                array_unshift($buyQueue, $lot);
            }
        }

        return $costBasis;
    }

    /**
     * Retrieves the historical prices of an asset via Yahoo Finance
     *
     * @param string $symbol
     * @return array
     */
    public function getHistoricalPrices(string $symbol): array
    {
        try {
            $client = ApiClientFactory::createApiClient();
            $historicalData = $client->getHistoricalQuoteData(
                $symbol,
                ApiClient::INTERVAL_1_DAY,
                new \DateTime('-30 days'), // Retrieve the last 30 days
                new \DateTime()
            );

            $prices = [];
            foreach ($historicalData as $day) {
                $prices[] = [strtotime($day->getDate()->format('Y-m-d')), $day->getClose()];
            }

            return $prices;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Analyzes the trend of an asset using OpenAI with historical prices
     *
     * @param array $prices
     * @return string
     */
    public function analyzeTrend(array $prices): string
    {
        if (empty($prices)) {
            return "unknown";
        }

        // Format the price data for the prompt
        $formattedPrices = "";
        foreach ($prices as $priceData) {
            $formattedPrices .= date('Y-m-d', $priceData[0]) . " : " . $priceData[1] . "\n";
        }

        // Prepare the prompt for OpenAI
        $prompt = "Here is the price history of an asset over the last 30 days:\n"
            . $formattedPrices
            . "\nAnalyze the overall trend of this asset."
            . " If the trend is upward, simply respond 'Bullish (Upward Trend)'."
            . " If the trend is downward, respond 'Bearish (Downward Trend)'."
            . " Do not add any other explanation.";

        // Call OpenAI API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a financial analyst.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.5,
            'max_tokens' => 10,
        ]);

        $result = $response->json();
        return strtolower(trim($result['choices'][0]['message']['content']));
    }

}
