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
     * Sync prices for all investments.
     */
    public function sync()
    {
        
        $this->priceSyncService->syncPricesForInvestments(Auth::id());
        return response()->json(['message' => 'Prices synchronized.'], 200);
        
    }
}
