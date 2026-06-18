<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesRingkasanSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        private readonly array $summary,
        private readonly \Illuminate\Support\Collection $byMethod,
        private readonly \Illuminate\Support\Collection $byProduct,
        private readonly string $from,
        private readonly string $to,
        private readonly string $outletName,
    ) {}

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 22,
            'C' => 22,
            'D' => 22,
        ];
    }

    public function array(): array
    {
        $period = \Carbon\Carbon::parse($this->from)->translatedFormat('d M Y')
            . ' – '
            . \Carbon\Carbon::parse($this->to)->translatedFormat('d M Y');

        $paymentLabels = [
            'cash'     => 'Tunai',
            'qris'     => 'QRIS',
            'transfer' => 'Transfer',
            'card'     => 'Kartu',
        ];

        $rows = [];

        // Header
        $rows[] = ['LAPORAN PENJUALAN', '', '', ''];
        $rows[] = [$this->outletName, '', '', ''];
        $rows[] = ['Periode: ' . $period, '', '', ''];
        $rows[] = ['', '', '', ''];

        // Ringkasan umum
        $rows[] = ['RINGKASAN', '', '', ''];
        $rows[] = ['Total Transaksi',   $this->summary['total_transactions'], '', ''];
        $rows[] = ['Total Pendapatan',  'Rp ' . number_format($this->summary['total_revenue'], 0, ',', '.'), '', ''];
        $rows[] = ['Total Diskon',      'Rp ' . number_format($this->summary['total_discount'], 0, ',', '.'), '', ''];
        $rows[] = ['Rata-rata/Transaksi','Rp ' . number_format($this->summary['avg_transaction'], 0, ',', '.'), '', ''];
        $rows[] = ['', '', '', ''];

        // Per Metode Bayar
        $rows[] = ['PENJUALAN PER METODE BAYAR', '', '', ''];
        $rows[] = ['Metode', 'Jumlah Transaksi', 'Total', '%'];
        foreach ($this->byMethod as $method => $data) {
            $pct = $this->summary['total_revenue'] > 0
                ? round($data['total'] / $this->summary['total_revenue'] * 100, 1)
                : 0;
            $rows[] = [
                $paymentLabels[$method] ?? $method,
                $data['count'],
                'Rp ' . number_format($data['total'], 0, ',', '.'),
                $pct . '%',
            ];
        }
        $rows[] = ['', '', '', ''];

        // Top Produk
        $rows[] = ['TOP PRODUK TERJUAL', '', '', ''];
        $rows[] = ['Produk', 'Qty Terjual', 'Pendapatan', '% Kontribusi'];
        foreach ($this->byProduct as $product) {
            $pct = $this->summary['total_revenue'] > 0
                ? round($product->total_revenue / $this->summary['total_revenue'] * 100, 1)
                : 0;
            $rows[] = [
                $product->product_name,
                $product->total_qty,
                'Rp ' . number_format($product->total_revenue, 0, ',', '.'),
                $pct . '%',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        // Title rows
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');

        return [
            1 => [
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8192C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font'      => ['bold' => true, 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8D7DA']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font'      => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '666666']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            5  => ['font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'E8192C']]],
            11 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'E8192C']]],
            12 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']]],
        ];
    }
}
