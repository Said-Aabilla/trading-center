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

}
