<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PriceSyncService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceSyncController extends Controller
{
    protected PriceSyncService $priceSyncService;

    public function __construct(PriceSyncService $priceSyncService)
    {
        $this->priceSyncService = $priceSyncService;
    }

    /**
     * Synchronise les prix pour tous les investissements.
     */
    public function sync()
    {
        $userId = Auth::id();
        try {
            $this->priceSyncService->syncPricesForInvestments($userId);
            return response()->json(['message' => 'Prix synchronisés avec succès.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la synchronisation des prix.'], 500);
        }
        
    }
}
