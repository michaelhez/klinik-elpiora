<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\ExcelService;
use App\Services\PdfService;
use App\Services\ReportService;

class ReportController extends Controller
{
    private const TYPES = ['revenue', 'visits', 'dispensing', 'doctors', 'inventory'];

    public function __construct(
        private readonly ReportService $service = new ReportService(),
        private readonly PdfService $pdfService = new PdfService(),
        private readonly ExcelService $excelService = new ExcelService(),
    ) {
    }

    public function index(Request $request): never
    {
        [$from, $to] = $this->resolveRange($request);

        $this->view('reports.index', [
            'title' => 'Laporan',
            'from' => $from,
            'to' => $to,
            'revenue' => $this->service->revenueReport($from, $to),
            'visits' => $this->service->patientVisitReport($from, $to),
            'dispensing' => $this->service->medicineDispensingReport($from, $to),
            'doctors' => $this->service->doctorPerformanceReport($from, $to),
            'inventory' => $this->service->inventoryReport(),
        ]);
    }

    public function export(Request $request, string $type): never
    {
        if (!in_array($type, self::TYPES, true)) {
            $this->fail('Jenis laporan tidak dikenal.', [], 422);
        }

        [$from, $to] = $this->resolveRange($request);
        $format = (string) $request->query('format', 'xlsx');

        [$title, $headers, $rows] = $this->buildTable($type, $from, $to);

        if ($format === 'pdf') {
            $pdf = $this->pdfService->render('reports.export_pdf', [
                'title' => $title,
                'headers' => $headers,
                'rows' => $rows,
                'from' => $from,
                'to' => $to,
            ], 'A4', 'landscape');

            $this->downloadFile($pdf, $this->filename($type, 'pdf'), 'application/pdf');
        }

        $xlsx = $this->excelService->export($title, $headers, $rows);
        $this->downloadFile($xlsx, $this->filename($type, 'xlsx'), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * @return array{0: string, 1: array<int, string>, 2: array<int, array<int, mixed>>}
     */
    private function buildTable(string $type, string $from, string $to): array
    {
        return match ($type) {
            'revenue' => (function () use ($from, $to) {
                $data = $this->service->revenueReport($from, $to);
                $rows = array_map(fn (array $r) => [$r['date'], format_currency((float) $r['total'])], $data['daily']);
                return ['Laporan Pendapatan', ['Tanggal', 'Total Pendapatan'], $rows];
            })(),
            'visits' => (function () use ($from, $to) {
                $data = $this->service->patientVisitReport($from, $to);
                $rows = array_map(fn (array $r) => [$r['polyclinic_name'], $r['total']], $data['by_polyclinic']);
                return ['Laporan Kunjungan Pasien', ['Poliklinik', 'Jumlah Kunjungan'], $rows];
            })(),
            'dispensing' => (function () use ($from, $to) {
                $data = $this->service->medicineDispensingReport($from, $to, 100);
                $rows = array_map(fn (array $r) => [$r['medicine_code'], $r['medicine_name'], $r['total_quantity'], $r['dispensing_count']], $data);
                return ['Laporan Obat Terlaris', ['Kode', 'Nama Obat', 'Jumlah Terjual', 'Jumlah Resep'], $rows];
            })(),
            'doctors' => (function () use ($from, $to) {
                $data = $this->service->doctorPerformanceReport($from, $to);
                $rows = array_map(fn (array $r) => [$r['doctor_name'], $r['polyclinic_name'], $r['total_patients']], $data);
                return ['Laporan Kinerja Dokter', ['Dokter', 'Poliklinik', 'Jumlah Pasien'], $rows];
            })(),
            'inventory' => (function () {
                $data = $this->service->inventoryReport();
                $rows = array_map(fn (array $r) => [$r['code'], $r['name'], $r['current_stock'], $r['minimum_stock']], $data['low_stock']);
                return ['Laporan Stok Menipis', ['Kode', 'Nama Obat', 'Stok Saat Ini', 'Stok Minimum'], $rows];
            })(),
        };
    }

    private function filename(string $type, string $extension): string
    {
        return "laporan-{$type}-" . date('Y-m-d') . ".{$extension}";
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveRange(Request $request): array
    {
        $from = (string) $request->query('from', date('Y-m-01'));
        $to = (string) $request->query('to', date('Y-m-d'));

        return [$from, $to];
    }
}
