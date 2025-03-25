<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\InvestmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    protected InvestmentService $investmentService;

    public function __construct(InvestmentService $investmentService)
    {
        $this->investmentService = $investmentService;
    }

    /**
     * Affiche la liste de tous les investissements.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $investments = $this->investmentService->getAllInvestmentsForUser(Auth::id());
        return response()->json($investments, 200);
    }

    /**
     * Enregistrement d'un investissement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:action,crypto',
            'symbol' => 'required|string',
            'quantity' => 'nullable|numeric',
            'sector' => 'nullable|string',
            'portfolio_id' => 'nullable|exists:portfolios,id',
            'current_price' => 'nullable|numeric',
            'cout_moyen' => 'nullable|numeric'
        ]);

        $investment = $this->investmentService->createInvestment($data);
        return response()->json(['message' => 'Investment successfully created.', 'data' => $investment], 201);
        
    }

    /**
     * Affiche un investissement spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        
        $investment = $this->investmentService->getInvestment(Auth::id(), $id);
        return response()->json($investment, 200);
        
    }

}
