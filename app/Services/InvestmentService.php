<?php

namespace App\Services;

use App\Models\Investment;
use Illuminate\Support\Facades\Auth;
use Exception;

class InvestmentService
{
    /**
     * Récupère tous les investissements de l'utilisateur.
     */
    public function getAllInvestmentsForUser(int $userId)
    {
        try {
            return Investment::where('user_id', $userId)->get();
        } catch (Exception $e) {
            throw new Exception('Error while fetching investments.');
        }
    }

    /**
     * Crée un nouvel investissement.
     */
    public function createInvestment(array $data)
    {
        try {
            $data['user_id'] = Auth::id();
            return Investment::create($data);
        } catch (Exception $e) {
            throw new Exception('Error while creating investment.');
        }
    }

    /**
     * Récupère un investissement spécifique.
     */
    public function getInvestment(int $userId, int $investmentId)
    {
        try {
            return Investment::where('user_id', $userId)->findOrFail($investmentId);
        } catch (Exception $e) {
            throw new Exception('Investissement not found.');
        }
    }

    /**
     * Met à jour un investissement.
     */
    public function updateInvestment(int $userId, int $investmentId, array $data)
    {
        try {
            $investment = $this->getInvestment($userId, $investmentId);
            $investment->update($data);
            return $investment;
        } catch (Exception $e) {
            throw new Exception('Error while updating investment.');
        }
    }

    /**
     * Supprime un investissement.
     */
    public function deleteInvestment(int $userId, int $investmentId)
    {
        try {
            $investment = $this->getInvestment($userId, $investmentId);
            $investment->delete();
            return true;
        } catch (Exception $e) {
            throw new Exception('Error while deleting investment.');
        }
    }
}
