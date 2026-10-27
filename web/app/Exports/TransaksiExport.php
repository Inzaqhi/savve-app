<?php

namespace App\Exports;

use App\Models\Transaksi;
use App\Models\Event;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class TransaksiExport
{
    protected $event_id;
    protected $tanggal;
    protected $tanggal_selesai;
    protected $status;

    public function __construct($event_id = null, $tanggal = null, $status = null, $tanggal_selesai = null)
    {
        $this->event_id        = $event_id;
        $this->tanggal         = $tanggal;
        $this->tanggal_selesai = $tanggal_selesai;
        $this->status          = $status;
    }

    public function download(string $filename)
    {
        \Carbon\Carbon::setLocale('id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        // ═══ KONFIGURASI LEBAR KOLOM ═══
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(22);  // No. Transaksi
        $sheet->getColumnDimension('C')->setWidth(20);  // Nama Penitip
        $sheet->getColumnDimension('D')->setWidth(12);  // Kategori
        $sheet->getColumnDimension('E')->setWidth(16);  // Total (Rp)
        $sheet->getColumnDimension('F')->setWidth(15);  // Metode Bayar

        // ═══ HEADER LAPORAN ═══
        $event = $this->event_id ? Event::find($this->event_id) : null;

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI PENITIPAN BARANG');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F2044']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Vendor Savve — Storage Management System');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => 'FFFFFF'], 'italic' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3A6B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // ═══ SPACER ═══
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFF']],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(8);

        // ═══ INFO LAPORAN (2 baris x 2 pasangan label/value) ═══
        $periode = 'Semua Tanggal';
        if ($this->tanggal && $this->tanggal_selesai) {
            $periode = \Carbon\Carbon::parse($this->tanggal)->format('d F Y') . ' - ' . \Carbon\Carbon::parse($this->tanggal_selesai)->format('d F Y');
        } elseif ($this->tanggal) {
            $periode = \Carbon\Carbon::parse($this->tanggal)->format('d F Y');
        } elseif ($event) {
            $periode = $event->tanggal_mulai->format('d F Y') . ' - ' . $event->tanggal_selesai->format('d F Y');
        }

        $statusText = $this->status ? match ($this->status) {
            'dititip'       => 'Dititipkan',
            'terlambat'     => 'Terlambat',
            'sudah_diambil' => 'Sudah Diambil',
            default         => ucfirst($this->status),
        } : 'Semua Status';

        $this->writeInfoPair($sheet, 'A', 4, 'Event:', $event ? $event->nama_event : 'Semua Event');
        $this->writeInfoPair($sheet, 'D', 4, 'Periode:', $periode);
        $this->writeInfoPair($sheet, 'A', 5, 'Status:', $statusText);
        $this->writeInfoPair($sheet, 'D', 5, 'Dicetak:', now()->translatedFormat('d F Y, H:i') . ' WIB');

        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(5)->setRowHeight(18);

        // ═══ SPACER ═══
        $sheet->mergeCells('A6:F6');
        $sheet->getStyle('A6')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFF']],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(8);

        // ═══ HEADER TABEL ═══
        $headerRow = 7;
        $headers = ['No', 'No. Transaksi', 'Nama Penitip', 'Kategori', 'Total (Rp)', 'Metode Bayar'];
        foreach ($headers as $i => $header) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . $headerRow, $header);
        }

        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3A6B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '2D5AA0']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        // ═══ DATA (satu baris per kategori/item) ═══
        $transaksis = $this->getData();
        $row = $headerRow + 1;
        $no = 1;

        foreach ($transaksis as $groupIndex => $t) {
            $isEvenGroup = ($groupIndex % 2 === 1);
            $bgColor = $isEvenGroup ? 'F8FAFF' : 'FFFFFF';

            $metodeBadge = match ($t->metode_bayar) {
                'Cash'  => ['label' => 'Cash',     'bg' => 'F0FDF4', 'color' => '15803D'],
                'QRIS'  => ['label' => 'QRIS',     'bg' => 'FAF5FF', 'color' => '7C3AED'],
                'Web'   => ['label' => 'Transfer', 'bg' => 'EFF6FF', 'color' => '1D4ED8'],
                default => ['label' => $t->metode_bayar ?? '-', 'bg' => 'F1F5F9', 'color' => '64748B'],
            };

            foreach ($t->details as $d) {
                $values = [
                    'A' => $no,
                    'B' => $t->nomor_transaksi,
                    'C' => $t->nama_penitip,
                    'D' => $d->ukuran,
                    'E' => $d->subtotal,
                    'F' => $metodeBadge['label'],
                ];

                foreach ($values as $col => $val) {
                    $sheet->setCellValue($col . $row, $val);
                }

                // Style per baris
                $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // No
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'font' => ['bold' => true, 'color' => ['rgb' => '94A3B8']],
                ]);
                // No. Transaksi
                $sheet->getStyle('B' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '1A3A6B'], 'name' => 'Courier New'],
                ]);
                // Kategori (badge)
                $sheet->getStyle('D' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '1D4ED8']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                // Total
                $sheet->getStyle('E' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '1A3A6B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'numberFormat' => ['formatCode' => '#,##0'],
                ]);
                // Metode Bayar (badge)
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $metodeBadge['color']]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $metodeBadge['bg']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getRowDimension($row)->setRowHeight(20);
                $row++;
                $no++;
            }
        }

        if ($transaksis->isEmpty()) {
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->setCellValue('A' . $row, 'Tidak ada data transaksi untuk filter yang dipilih.');
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => '94A3B8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(28);
            $row++;
        }

        // ═══ SPACER ═══
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFF']],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(8);
        $row++;

        // ═══ FOOTER SUMMARY — baris 1: Total Transaksi / Dititipkan / Diambil ═══
        $summaryData = [
            ['Total Transaksi',  $transaksis->count()],
            ['Total Dititipkan', $transaksis->where('status', 'dititip')->count()],
            ['Total Diambil',    $transaksis->where('status', 'sudah_diambil')->count()],
        ];

        foreach ($summaryData as $i => $s) {
            $col1 = chr(65 + ($i * 2));
            $col2 = chr(66 + ($i * 2));
            $sheet->setCellValue($col1 . $row, $s[0]);
            $sheet->setCellValue($col2 . $row, $s[1]);
            $sheet->getStyle($col1 . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
            ]);
            $sheet->getStyle($col2 . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F2044']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        // ═══ FOOTER SUMMARY — baris 2: Total Pendapatan (highlight) ═══
        $totalPendapatan = $transaksis->sum(fn($t) => $t->total_harga);

        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->setCellValue('A' . $row, 'Total Pendapatan');
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '0F2044']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->mergeCells('C' . $row . ':F' . $row);
        $sheet->setCellValue('C' . $row, 'Rp ' . number_format($totalPendapatan, 0, ',', '.'));
        $sheet->getStyle('C' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '15803D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(28);
        $row++;

        // ═══ COPYRIGHT ═══
        $row++;
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValue('A' . $row, '© ' . date('Y') . ' Vendor Savve — Storage Management System');
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => '94A3B8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ═══ FREEZE PANES & PRINT SETTINGS ═══
        $sheet->freezePane('A' . ($headerRow + 1));
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getHeaderFooter()->setOddHeader('&C&B Laporan Transaksi — Vendor Savve');
        $sheet->getHeaderFooter()->setOddFooter('&L&D &T&R Halaman &P dari &N');

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Tulis satu pasang label/value pada baris info.
     * Label di kolom $startCol, value digabung pada 2 kolom berikutnya.
     */
    private function writeInfoPair($sheet, string $startCol, int $rowNum, string $label, string $value): void
    {
        $startIndex = ord($startCol) - 65;
        $valueColStart = chr(65 + $startIndex + 1);
        $valueColEnd   = chr(65 + $startIndex + 2);

        $sheet->setCellValue($startCol . $rowNum, $label);
        $sheet->getStyle($startCol . $rowNum)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
        ]);

        $sheet->mergeCells($valueColStart . $rowNum . ':' . $valueColEnd . $rowNum);
        $sheet->setCellValue($valueColStart . $rowNum, $value);
        $sheet->getStyle($valueColStart . $rowNum)->applyFromArray([
            'font' => ['size' => 9, 'color' => ['rgb' => '1E293B']],
        ]);
    }

    private function getData()
    {
        $query = Transaksi::with(['details']);

        if ($this->event_id) {
            $query->where('event_id', $this->event_id);
        }

        if ($this->tanggal && $this->tanggal_selesai) {
            $query->whereBetween('waktu_penitipan', [
                $this->tanggal . ' 00:00:00',
                $this->tanggal_selesai . ' 23:59:59',
            ]);
        } elseif ($this->tanggal) {
            $query->whereDate('waktu_penitipan', $this->tanggal);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest('waktu_penitipan')->get();
    }
}
