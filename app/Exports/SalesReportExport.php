<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements WithMultipleSheets
{
    public function __construct(
        private readonly array $summary,
        private readonly \Illuminate\Support\Collection $byMethod,
        private readonly \Illuminate\Support\Collection $byProduct,
        private readonly \Illuminate\Support\Collection $transactions,
        private readonly string $from,
        private readonly string $to,
        private readonly string $outletName,
    ) {}

    public function sheets(): array
    {
        return [
            new SalesRingkasanSheet(
                $this->summary,
                $this->byMethod,
                $this->byProduct,
                $this->from,
                $this->to,
                $this->outletName,
            ),
            new SalesDetailSheet(
                $this->transactions,
                $this->from,
                $this->to,
            ),
        ];
    }
}
