<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Affiche toutes les transactions pour un utilisateur.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $transactions = $this->transactionService->getAllTransactionsForUser(Auth::id());
        
        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'No transactions found.'], 404);
        }

        return response()->json(['data' => $transactions], 200);
    }

    /**
     * Crée une nouvelle transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'investment_id'    => 'nullable|exists:investments,id',
        'type'             => 'required|in:buy,sell',
        'quantity'         => 'required|numeric',
        'price'            => 'required|numeric',
        'transaction_date' => 'required|date'
    ]);

    // Sélectionner la méthode de création de transaction en fonction du type
    if ($data['type'] === 'buy') {
        $transaction = $this->transactionService->createBuyTransaction($data);
    } else { // 'sell'
        $transaction = $this->transactionService->createSellTransaction($data);
    }

    return response()->json([
        'message' => 'Transaction créée avec succès.',
        'data'    => $transaction
    ], 201);
}


}
