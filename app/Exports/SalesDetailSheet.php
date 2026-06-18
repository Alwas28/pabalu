<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesDetailSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    public function __construct(
        private readonly \Illuminate\Support\Collection $transactions,
        private readonly string $from,
        private readonly string $to,
    ) {}

    public function title(): string
    {
        return 'Detail Transaksi';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 22,
            'C' => 16,
            'D' => 14,
            'E' => 36,
            'F' => 18,
            'G' => 18,
            'H' => 16,
            'I' => 16,
            'J' => 18,
        ];
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            '#',
            'No. Transaksi',
            'Tanggal',
            'Jam',
            'Item',
            'Subtotal',
            'Diskon',
            'Total',
            'Metode Bayar',
            'Kasir',
        ];
    }

    public function map($trx): array
    {
        static $no = 0;
        $no++;

        $paymentLabels = [
            'cash'     => 'Tunai',
            'qris'     => 'QRIS',
            'transfer' => 'Transfer',
            'card'     => 'Kartu',
        ];

        $itemNames = $trx->items->map(fn($i) => $i->product_name . ' x' . $i->qty)->implode(', ');

        return [
            $no,
            $trx->transaction_number,
            $trx->date->format('d/m/Y'),
            $trx->created_at->format('H:i'),
            $itemNames,
            (int) $trx->subtotal,
            (int) $trx->discount_amount,
            (int) $trx->total,
            $paymentLabels[$trx->payment_method] ?? $trx->payment_method,
            $trx->user?->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8192C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
