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
     Route::get('/portfolios', [PortfolioController::class, 'index']);
     Route::post('/portfolios', [PortfolioController::class, 'create']);
     Route::get('/portfolios/{portfolioId}', [PortfolioController::class, 'show']);
     Route::delete('/portfolios/{portfolioId}', [PortfolioController::class, 'destroy']);
    
    // Investment Management Routes
    Route::prefix('investments')->group(function () {
        Route::get('/', [InvestmentController::class, 'index']);
        Route::post('/', [InvestmentController::class, 'store']);
        Route::get('{id}', [InvestmentController::class, 'show']);
    });

    // Transaction Management Routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('{id}', [TransactionController::class, 'show']);
    });

    // Price Synchronization Routes

    Route::post('/sync-prices', [PriceSyncController::class, 'sync']);

    // Analysis Routes
    Route::prefix('analyse')->group(function () {

        // Portfolio Distribution Analysis
        Route::get('/repartition-portefeuille', [AnalyseController::class, 'repartitionPortefeuille']);

        // ROI Analysis
        Route::get('/roi-non-encaisse/{investmentId}', [AnalyseController::class, 'roiNonEncaisse']);
        Route::get('/roi-encaisse/{investmentId}', [AnalyseController::class, 'roiEncaisse']);

        // Sector Performance Analysis
        Route::get('/sector-performance', [AnalyseController::class, 'performanceBySector']);

        // Cash Gain and Non-Cash Gain Analysis
        Route::get('/gain-encaisse/{investmentId}', [AnalyseController::class, 'gainEncaisse']);
        Route::get('/gain-non-encaisse/{investmentId}', [AnalyseController::class, 'gainNonEncaisse']);

        // Trending Analysis
        Route::post('/tendance', [AnalyseController::class, 'analyseTendance']);
    });

    // Transaction Analysis Routes
    Route::prefix('analyse-transaction')->group(function () {
        // Total Capital Gains
        Route::get('/total-capital-gains', [TransactionAnalyseController::class, 'totalCapitalGains']);

        // Generate Tax Report
        Route::get('/tax-report', [TransactionAnalyseController::class, 'generateTaxReport']);
    });

});
