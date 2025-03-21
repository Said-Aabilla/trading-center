<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PriceSyncService;
use Illuminate\Console\Command;

class SyncPriceStocks extends Command
{
    protected $signature = 'actions:cron';
    protected $description = 'Synchroniser les prix de tous les investissements pour tous les utilisateurs';

    protected PriceSyncService $priceSyncService;

    public function __construct(PriceSyncService $priceSyncService)
    {
        parent::__construct();
        $this->priceSyncService = $priceSyncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        info("Cron Job running at ". now());
        // Récupère tous les utilisateurs
        $users = User::all();

        if (!empty($users)) {  
            foreach ($users as $user) {
                $this->priceSyncService->syncPricesForInvestments($user->id);
                $this->info("Prix synchronisés pour l'utilisateur ID: {$user->id}");
            }
        }

    }
}
