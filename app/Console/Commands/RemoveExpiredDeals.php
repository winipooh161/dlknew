<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deal;

class RemoveExpiredDeals extends Command
{
    protected $signature = 'deals:remove-expired';
    protected $description = 'Удаление просроченных сделок';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $expiredDeals = Deal::where('expiry_date', '<', now())->get();

        foreach ($expiredDeals as $deal) {
            $deal->delete();
        }

        $this->info('Просроченные сделки успешно удалены.');
    }
}
