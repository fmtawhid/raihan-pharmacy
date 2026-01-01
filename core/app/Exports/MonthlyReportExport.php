<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MonthlyReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        private Collection $rows,
        private object     $stats          // company-wide totals
    ) {}

    public function collection(): Collection
    {
        // First row = grand totals, then each admin
        return collect([
            ['TOTAL', $this->stats->contacted, $this->stats->potential]
        ])->merge(
            $this->rows->map(fn ($r) => [
                $r->admin->name,
                $r->contacted,
                $r->potential
            ])
        );
    }

    public function headings(): array
    {
        return ['Employee', 'Customers Contacted', 'Potential Customers'];
    }
}
