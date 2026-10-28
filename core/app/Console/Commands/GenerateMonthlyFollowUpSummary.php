<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FollowUpLog;
use App\Models\MonthlyFollowUpSummary;

class GenerateMonthlyFollowUpSummary extends Command
{
    protected $signature = 'followup:daily-rollup';
    protected $description = 'Aggregate yesterday’s follow-up logs into the monthly summary table';

    public function handle(): int
    {
        // yesterday’s range
        $start = now()->subDay()->startOfDay();
        $end   = now()->subDay()->endOfDay();
        $monthKey = $start->format('Y-m');          // e.g. "2025-06"

        $rows = FollowUpLog::whereBetween('contact_date', [$start, $end])
            ->groupBy('admin_id')
            ->selectRaw('admin_id,
                         SUM(customers_contacted)  contacted_total,
                         SUM(potential_customers)  potential_total')
            ->get();

        foreach ($rows as $r) {
            MonthlyFollowUpSummary::updateOrCreate(
                ['month' => $monthKey, 'admin_id' => $r->admin_id],
                [
                    'contacted_total'  => \DB::raw("contacted_total + {$r->contacted_total}"),
                    'potential_total'  => \DB::raw("potential_total + {$r->potential_total}"),
                ]
            );
        }

        $this->info("Daily roll-up added to {$monthKey}");
        return self::SUCCESS;
    }
}

