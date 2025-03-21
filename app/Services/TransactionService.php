<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Récupère toutes les transactions de l'utilisateur.
     */
    public function getAllTransactionsForUser(int $userId)
    {
        try {
            return Transaction::where('user_id', $userId)->get();
        } catch (Exception $e) {
            throw new Exception('Error while fetching transactions.');
        }
    }

    /**
     * Crée une nouvelle transaction.
     */
    public function createTransaction(array $data)
    {
        DB::beginTransaction();
        try {
            // Associer l'utilisateur connecté
            $data['user_id'] = Auth::id();

            // Si un investissement est concerné, l'ajuster
            if (isset($data['investment_id'])) {
                $investment = Investment::where('user_id', $data['user_id'])
                    ->find($data['investment_id']);

                if ($investment) {
                    if ($data['type'] === 'buy') {
                        // Augmenter la quantité en cas d'achat
                        $investment->quantity += $data['quantity'];
                    } elseif ($data['type'] === 'sell') {
                        // Vérifier que la quantité est suffisante pour la vente
                        if ($investment->quantity < $data['quantity']) {
                            throw new Exception("Quantité insuffisante pour la vente.");
                        }
                        // Décrémenter la quantité en cas de vente
                        $investment->quantity -= $data['quantity'];
                    }
                    $investment->save();
                }
            }

            // Créer la transaction
            $transaction = Transaction::create($data);
            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Error while creating transaction.');
        }
    }

    /**
     * Récupère une transaction spécifique.
     */
    public function getTransaction(int $userId, int $transactionId)
    {
        try {
            return Transaction::where('user_id', $userId)->findOrFail($transactionId);
        } catch (Exception $e) {
            throw new Exception('Transaction not found.');
        }
    }

    /**
     * Met à jour une transaction.
     */
    public function updateTransaction(int $userId, int $transactionId, array $data)
    {
        try {
            $transaction = $this->getTransaction($userId, $transactionId);
            $transaction->update($data);
            return $transaction;
        } catch (Exception $e) {
            throw new Exception('Error while updating transaction.');
        }
    }

    /**
     * Supprime une transaction.
     */
    public function deleteTransaction(int $userId, int $transactionId)
    {
        try {
            $transaction = $this->getTransaction($userId, $transactionId);
            $transaction->delete();
            return true;
        } catch (Exception $e) {
            throw new Exception('Error while deleting transaction.');
        }
    }
}
