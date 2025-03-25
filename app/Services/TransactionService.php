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
            throw new Exception('Erreur lors de la récupération des transactions: ' . $e->getMessage());
        }
    }

    /**
     * Crée une transaction d'achat et met à jour l'investissement en utilisant le coût moyen pondéré.
     *
     * Calcul du nouveau coût moyen :
     *   totalDépense = (ancienCoutMoyen * ancienneQuantité) + (prixAchat * quantitéAchetée)
     *   nouvelleQuantité = ancienneQuantité + quantitéAchetée
     *   nouveauCoutMoyen = totalDépense / nouvelleQuantité
     *
     * @param array $data Les données de la transaction (incluant investment_id, price, quantity, type = "buy")
     * @return Transaction
     * @throws Exception
     */
    public function createBuyTransaction(array $data)
    {
        DB::beginTransaction();
        try {
            // Associer l'utilisateur connecté
            $data['user_id'] = Auth::id();
            
            if (isset($data['investment_id'])) {
                $investment = Investment::where('user_id', $data['user_id'])
                    ->find($data['investment_id']);
                    
                if (!isset($data['investment_id'])) {
                    throw new Exception("Aucun investissement spécifié.");
                }

                $investment = Investment::where('user_id', $data['user_id'])
                ->where('id', $data['investment_id'])
                ->first();

                if (!$investment) {
                    throw new Exception("Investissement introuvable ou non autorisé.");
                }
                
                // Récupérer l'état actuel de l'investissement
                $ancienneQuantite = $investment->quantity;
                $ancienCoutMoyen  = $investment->cout_moyen; // 0 s'il s'agit du premier achat
                
                // Valeur du nouvel achat
                $quantiteAchetee = $data['quantity'];
                $prixAchat = $data['price'];
                
                // Calcul du coût moyen pondéré
                $totalDepense = ($ancienCoutMoyen * $ancienneQuantite) + ($prixAchat * $quantiteAchetee);
                $nouvelleQuantite = $ancienneQuantite + $quantiteAchetee;
                $nouveauCoutMoyen = $nouvelleQuantite > 0 ? $totalDepense / $nouvelleQuantite : 0;
                
                // Mise à jour de l'investissement
                $investment->quantity = $nouvelleQuantite;
                $investment->cout_moyen = $nouveauCoutMoyen;
                $investment->save();
            }
            
            // Créer la transaction d'achat
            $transaction = Transaction::create($data);
            DB::commit();
            return $transaction;
        } catch(Exception $e) {
            DB::rollBack();
            throw new Exception('Erreur lors de la création de la transaction d\'achat: ' . $e->getMessage());
        }
    }
    
    /**
     * Crée une transaction de vente et met à jour l'investissement.
     * Pour la vente, on vérifie la quantité disponible et on décrémente la quantité vendue.
     * Le coût moyen (average_cost) reste inchangé.
     *
     * @param array $data Les données de la transaction (incluant investment_id, price, quantity, type = "sell")
     * @return Transaction
     * @throws Exception
     */
    public function createSellTransaction(array $data)
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = Auth::id();

            if (!isset($data['investment_id'])) {
                throw new Exception("Aucun investissement spécifié.");
            }

            // Vérifier que l'investissement appartient à l'utilisateur
            $investment = Investment::where('user_id', $data['user_id'])
                ->where('id', $data['investment_id'])
                ->first();

            if (!$investment) {
                throw new Exception("Investissement introuvable ou non autorisé.");
            }

            // Vérifier la quantité disponible pour la vente
            if ($investment->quantity < $data['quantity']) {
                throw new Exception("Quantité insuffisante pour la vente.");
            }

            // Mettre à jour la quantité
            $investment->quantity -= $data['quantity'];
            $investment->save();

            // Créer la transaction de vente
            $transaction = Transaction::create($data);

            DB::commit();
            return $transaction;
        } catch(Exception $e) {
            DB::rollBack();
            throw new Exception('Erreur lors de la création de la transaction de vente: ' . $e->getMessage());
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
            throw new Exception('Transaction non trouvée: ' . $e->getMessage());
        }
    }
}
