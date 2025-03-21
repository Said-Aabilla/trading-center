<?php

use App\Http\Controllers\AnalyseController;
use App\Http\Controllers\TransactionAnalyseController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\PriceSyncController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortfolioController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Routes protected by Sanctum Authentication
Route::middleware('auth:sanctum')->group(function () {

     // Portfolio Routes
     Route::get('/portfolios', [PortfolioController::class, 'index']); // List all portfolios
     Route::post('/portfolios', [PortfolioController::class, 'create']); // Create a new portfolio
     Route::get('/portfolios/{portfolioId}', [PortfolioController::class, 'show']); // Get a specific portfolio
     Route::put('/portfolios/{portfolioId}', [PortfolioController::class, 'update']); // Update a portfolio
     Route::delete('/portfolios/{portfolioId}', [PortfolioController::class, 'destroy']); // Delete a portfolio
     Route::get('/portfolios/{portfolioId}/assets', [PortfolioController::class, 'getAssets']); // Get assets in a portfolio
    
    // Investment Management Routes
    Route::prefix('investments')->group(function () {
        Route::get('/', [InvestmentController::class, 'index']);
        Route::post('/', [InvestmentController::class, 'store']);
        Route::get('{id}', [InvestmentController::class, 'show']);
        Route::put('{id}', [InvestmentController::class, 'update']);
        Route::delete('{id}', [InvestmentController::class, 'destroy']);
    });

    // Transaction Management Routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('{id}', [TransactionController::class, 'show']);
        Route::put('{id}', [TransactionController::class, 'update']);
        Route::delete('{id}', [TransactionController::class, 'destroy']);
    });

    // Price Synchronization Routes
    Route::prefix('prices')->group(function () {
        Route::post('/sync', [PriceSyncController::class, 'sync']);
    });

    // Analysis Routes
    Route::prefix('analyses')->group(function () {

        // Portfolio Distribution Analysis
        Route::get('/portfolio-distribution', [AnalyseController::class, 'portfolioDistribution']);

        // ROI Analysis
        Route::get('/unrealized-roi/{invest_id}', [AnalyseController::class, 'unrealizedROI']);
        Route::get('/realized-roi/{invest_id}', [AnalyseController::class, 'realizedROI']);

        // Sector Performance Analysis
        Route::get('/performance-by-sector', [AnalyseController::class, 'performanceBySector']);

        // Cash Gain and Non-Cash Gain Analysis
        Route::get('/cash-gain/{invest_id}', [AnalyseController::class, 'cashGain']);
        Route::get('/non-cash-gain/{invest_id}', [AnalyseController::class, 'nonCashGain']);

        // Trending Analysis
        Route::post('/trending', [AnalyseController::class, 'trendingAnalysis']);
    });

    // Transaction Analysis Routes
    Route::prefix('analyse-transaction')->group(function () {
        // Total Capital Gains
        Route::get('/total-capital-gains', [TransactionAnalyseController::class, 'totalCapitalGains']);

        // Generate Tax Report
        Route::get('/generateTaxReport', [TransactionAnalyseController::class, 'generateTaxReport']);
    });

});
