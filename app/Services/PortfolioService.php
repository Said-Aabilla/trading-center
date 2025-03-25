<?php

namespace App\Services;

use App\Models\Portfolio;


class PortfolioService
{
    public function createPortfolio($userId, $name)
    {
        $portfolio = new Portfolio();
        $portfolio->user_id = $userId;
        $portfolio->name = $name;
        $portfolio->save();
        return $portfolio;
    }
    
    public function getPortfolio($portfolioId)
    {
        return Portfolio::findOrFail($portfolioId);
    }

    public function updatePortfolio($portfolioId, $name)
    {
        $portfolio = Portfolio::findOrFail($portfolioId);
        $portfolio->name = $name;
        $portfolio->save();
        return $portfolio;
    }

    public function deletePortfolio($portfolioId)
    {
        $portfolio = Portfolio::findOrFail($portfolioId);
        $portfolio->delete();
        return $portfolio;
    }

    public function getPortfolioAssets($portfolioId)
    {
        return Portfolio::findOrFail($portfolioId)->investments;
    }
}
