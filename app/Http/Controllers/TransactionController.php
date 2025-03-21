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
     * Display all transactions for a user.
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
     * Store a new transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'investment_id' => 'nullable|exists:investments,id',
            'type' => 'required|in:buy,sell',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'transaction_date' => 'required|date'
        ]);

        $transaction = $this->transactionService->createTransaction($data);

        return response()->json([
            'message' => 'Transaction successfully created.',
            'data' => $transaction
        ], 201);
    }

    /**
     * Update an existing transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'investment_id' => 'nullable|exists:investments,id',
            'type'  => 'in:buy,sell',
            'quantity' => 'numeric',
            'price' => 'numeric',
            'transaction_date' => 'date'
        ]);

        $updated_transaction = $this->transactionService->updateTransaction(Auth::id(), $id, $data);

        return response()->json([
            'message' => 'Transaction successfully updated.',
            'data' => $updated_transaction
        ], 200);
    }

    /**
     * Display a specific transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $transaction = $this->transactionService->getTransaction(Auth::id(), $id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        return response()->json([
            'message' => 'Transaction successfully retrieved.',
            'data' => $transaction
        ], 200);
    }

    /**
     * Delete a specific transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $deleted = $this->transactionService->deleteTransaction(Auth::id(), $id);

        if (!$deleted) {
            return response()->json(['message' => 'Error occurred while deleting the transaction.'], 400);
        }

        return response()->json([
            'message' => 'Transaction successfully deleted.'
        ], 204);
    }
}
