<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\InvestmentService;
use Exception;
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
            'current_price' => 'nullable|numeric'
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

    /**
     * Modification d'un investissement.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'type' => 'in:action,crypto',
            'symbol' => 'string',
            'quantity' => 'numeric',
            'current_price' => 'numeric',
            'portfolio_id' => 'nullable|exists:portfolios,id',
            'sector'  => 'nullable|string'
        ]);
        
        $updatedInvestment = $this->investmentService->updateInvestment(Auth::id(), $id, $data);
        return response()->json(['message' => 'Investment successfully updated.', 'data' => $updatedInvestment], 200);
    }

    /**
     * Suppression d'un investissement.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        
        $this->investmentService->deleteInvestment(Auth::id(), $id);
        return response()->json(['message' => 'Investment successfully deleted.'], 200);
       
    }
}
