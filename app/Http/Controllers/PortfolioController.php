<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PortfolioService;
use Illuminate\Support\Facades\Auth;

class PortfolioController extends Controller
{

    protected $portfolioService;

    public function __construct(PortfolioService $portfolioService)
    {
        $this->portfolioService = $portfolioService;
    }

   /**
     * Crée un nouveau portefeuille pour l'utilisateur.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $userId = Auth::id();
        $name = $request->input('name');
        $portfolio = $this->portfolioService->createPortfolio($userId, $name);
        return response()->json($portfolio, 201);
    }

    /**
     * Récupère un portefeuille spécifique par ID.
     *
     * @param $portfolioId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($portfolioId)
    {
        $portfolio = $this->portfolioService->getPortfolio($portfolioId);
        return response()->json($portfolio, 200);
    }

    /**
     * Met à jour le nom d'un portefeuille.
     *
     * @param Request $request
     * @param $portfolioId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $portfolioId)
    {
        $name = $request->input('name');
        $portfolio = $this->portfolioService->updatePortfolio($portfolioId, $name);
        return response()->json($portfolio, 200);
    }

    /**
     * Supprime un portefeuille spécifique.
     *
     * @param $portfolioId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($portfolioId)
    {
        $this->portfolioService->deletePortfolio($portfolioId);
        return response()->json(['message' => 'Portfolio deleted successfully'], 200);
    }

    /**
     * Récupère tous les actifs d'un portefeuille spécifique.
     *
     * @param $portfolioId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssets($portfolioId)
    {
        $assets = $this->portfolioService->getPortfolioAssets($portfolioId);
        return response()->json($assets, 200);
    }
}