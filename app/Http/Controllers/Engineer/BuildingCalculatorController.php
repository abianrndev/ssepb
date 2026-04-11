<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\BuildingEstimation;
use App\Models\BuildingEstimationDeduction;
use App\Models\BuildingEstimationItem;
use App\Models\PriceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class BuildingCalculatorController extends Controller
{
    public function showCalculator(Request $request): Response
    {
        $result = null;
        if ($request->filled('result')) {
            $decoded = json_decode(base64_decode($request->query('result')), true);
            if (is_array($decoded)) $result = $decoded;
        }

        $mutuOptions = PriceSetting::query()
            ->where('is_active', true)
            ->orderBy('mutu_beton')
            ->pluck('mutu_beton');

        return Inertia::render('Engineer/Building/BuildingCalculator', [
            'mutuOptions' => $mutuOptions,
            'result' => $result,
            'success' => $request->query('success'),
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'nama_proyek' => ['nullable', 'string', 'max:255'],
            'lokasi_proyek' => ['nullable', 'string', 'max:255'],
            'jumlah_lantai' => ['required', 'integer', 'min:1'],
            'mutu_beton' => ['required', 'string', 'max:50'],
            'waste_percent' => ['required', 'numeric', 'gte:0', 'lte:50'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.jenis_item' => ['required', 'string', 'max:50'],
            'items.*.nama_item' => ['nullable', 'string', 'max:255'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.panjang_m' => ['required', 'numeric', 'gt:0'],
            'items.*.lebar_m' => ['required', 'numeric', 'gt:0'],
            'items.*.tebal_m' => ['required', 'numeric', 'gt:0'],
            'items.*.keterangan' => ['nullable', 'string', 'max:1000'],

            'deductions' => ['nullable', 'array'],
            'deductions.*.jenis_pengurang' => ['nullable', 'string', 'max:100'],
            'deductions.*.jumlah' => ['nullable', 'integer', 'min:1'],
            'deductions.*.panjang_m' => ['nullable', 'numeric', 'gt:0'],
            'deductions.*.lebar_m' => ['nullable', 'numeric', 'gt:0'],
            'deductions.*.tebal_m' => ['nullable', 'numeric', 'gt:0'],
            'deductions.*.keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $itemRows = [];
        $volumeKotor = 0.0;

        foreach ($validated['items'] as $it) {
            $jumlah = (int) $it['jumlah'];
            $panjang = (float) $it['panjang_m'];
            $lebar = (float) $it['lebar_m'];
            $tebal = (float) $it['tebal_m'];
            $volume = $jumlah * $panjang * $lebar * $tebal;

            $volumeKotor += $volume;

            $itemRows[] = [
                'jenis_item' => $it['jenis_item'],
                'nama_item' => $it['nama_item'] ?? null,
                'jumlah' => $jumlah,
                'panjang_m' => $panjang,
                'lebar_m' => $lebar,
                'tebal_m' => $tebal,
                'volume_m3' => $volume,
                'keterangan' => $it['keterangan'] ?? null,
            ];
        }

        $deductionRows = [];
        $volumePengurang = 0.0;

        foreach (($validated['deductions'] ?? []) as $d) {
            $jenis = $d['jenis_pengurang'] ?? null;
            $jumlah = (int) ($d['jumlah'] ?? 0);
            $panjang = (float) ($d['panjang_m'] ?? 0);
            $lebar = (float) ($d['lebar_m'] ?? 0);
            $tebal = (float) ($d['tebal_m'] ?? 0);

            if (!$jenis || $jumlah <= 0 || $panjang <= 0 || $lebar <= 0 || $tebal <= 0) continue;

            $volume = $jumlah * $panjang * $lebar * $tebal;
            $volumePengurang += $volume;

            $deductionRows[] = [
                'jenis_pengurang' => $jenis,
                'jumlah' => $jumlah,
                'panjang_m' => $panjang,
                'lebar_m' => $lebar,
                'tebal_m' => $tebal,
                'volume_m3' => $volume,
                'keterangan' => $d['keterangan'] ?? null,
            ];
        }

        $volumeBersih = max($volumeKotor - $volumePengurang, 0);
        $wastePercent = (float) $validated['waste_percent'];
        $wasteVolume = $volumeBersih * ($wastePercent / 100);
        $totalAkhir = $volumeBersih + $wasteVolume;

        $hargaPerM3 = (float) (PriceSetting::query()
            ->where('mutu_beton', $validated['mutu_beton'])
            ->where('is_active', true)
            ->value('harga_per_m3') ?? 0);

        $estimasiHargaTotal = $totalAkhir * $hargaPerM3;

        DB::transaction(function () use ($request, $validated, $volumeKotor, $volumePengurang, $volumeBersih, $wasteVolume, $totalAkhir, $hargaPerM3, $estimasiHargaTotal, $itemRows, $deductionRows) {
            $est = BuildingEstimation::create([
                'user_id' => $request->user()->id,
                'nama_proyek' => $validated['nama_proyek'] ?? null,
                'lokasi_proyek' => $validated['lokasi_proyek'] ?? null,
                'jumlah_lantai' => $validated['jumlah_lantai'],
                'mutu_beton' => $validated['mutu_beton'],
                'waste_percent' => $validated['waste_percent'],
                'volume_kotor' => $volumeKotor,
                'volume_pengurang' => $volumePengurang,
                'volume_bersih' => $volumeBersih,
                'waste_volume' => $wasteVolume,
                'total_akhir_m3' => $totalAkhir,
                'harga_per_m3' => $hargaPerM3,
                'estimasi_harga_total' => $estimasiHargaTotal,
            ]);

            foreach ($itemRows as $row) {
                $row['building_estimation_id'] = $est->id;
                BuildingEstimationItem::create($row);
            }

            foreach ($deductionRows as $row) {
                $row['building_estimation_id'] = $est->id;
                BuildingEstimationDeduction::create($row);
            }
        });

        $result = [
            'volume_kotor' => round($volumeKotor, 4),
            'volume_pengurang' => round($volumePengurang, 4),
            'volume_bersih' => round($volumeBersih, 4),
            'waste_volume' => round($wasteVolume, 4),
            'total_akhir_m3' => round($totalAkhir, 4),
            'harga_per_m3' => round($hargaPerM3, 2),
            'estimasi_harga_total' => round($estimasiHargaTotal, 2),
            'mutu_beton' => $validated['mutu_beton'],
        ];

        return redirect()->route('engineer.building.form', [
            'success' => 'Estimasi cor rumah/gedung berhasil dihitung & disimpan.',
            'result' => base64_encode(json_encode($result)),
        ]);
    }

    public function history(Request $request): Response
    {
        $query = BuildingEstimation::query()
            ->where('user_id', $request->user()->id);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('mutu_beton')) {
            $query->where('mutu_beton', $request->mutu_beton);
        }

        $rows = $query->latest()->get()->map(fn ($r) => [
            'id' => $r->id,
            'created_at' => optional($r->created_at)->format('Y-m-d H:i:s'),
            'nama_proyek' => $r->nama_proyek,
            'lokasi_proyek' => $r->lokasi_proyek,
            'jumlah_lantai' => (int) $r->jumlah_lantai,
            'mutu_beton' => $r->mutu_beton,
            'total_akhir_m3' => (float) $r->total_akhir_m3,
            'estimasi_harga_total' => (float) $r->estimasi_harga_total,
        ])->values();

        return Inertia::render('Engineer/Building/BuildingHistory', [
            'histories' => $rows,
            'filters' => [
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', ''),
                'mutu_beton' => $request->get('mutu_beton', ''),
            ],
        ]);
    }

    public function detail(Request $request, BuildingEstimation $buildingEstimation): Response
    {
        abort_if($buildingEstimation->user_id !== $request->user()->id, 403);

        $buildingEstimation->load(['items', 'deductions']);

        return Inertia::render('Engineer/Building/BuildingDetail', [
            'item' => [
                'id' => $buildingEstimation->id,
                'created_at' => optional($buildingEstimation->created_at)->format('Y-m-d H:i:s'),
                'nama_proyek' => $buildingEstimation->nama_proyek,
                'lokasi_proyek' => $buildingEstimation->lokasi_proyek,
                'jumlah_lantai' => (int) $buildingEstimation->jumlah_lantai,
                'mutu_beton' => $buildingEstimation->mutu_beton,
                'waste_percent' => (float) $buildingEstimation->waste_percent,

                'volume_kotor' => (float) $buildingEstimation->volume_kotor,
                'volume_pengurang' => (float) $buildingEstimation->volume_pengurang,
                'volume_bersih' => (float) $buildingEstimation->volume_bersih,
                'waste_volume' => (float) $buildingEstimation->waste_volume,
                'total_akhir_m3' => (float) $buildingEstimation->total_akhir_m3,
                'harga_per_m3' => (float) $buildingEstimation->harga_per_m3,
                'estimasi_harga_total' => (float) $buildingEstimation->estimasi_harga_total,

                'items' => $buildingEstimation->items->map(fn ($x) => [
                    'id' => $x->id,
                    'jenis_item' => $x->jenis_item,
                    'nama_item' => $x->nama_item,
                    'jumlah' => (int) $x->jumlah,
                    'panjang_m' => (float) $x->panjang_m,
                    'lebar_m' => (float) $x->lebar_m,
                    'tebal_m' => (float) $x->tebal_m,
                    'volume_m3' => (float) $x->volume_m3,
                    'keterangan' => $x->keterangan,
                ])->values(),

                'deductions' => $buildingEstimation->deductions->map(fn ($x) => [
                    'id' => $x->id,
                    'jenis_pengurang' => $x->jenis_pengurang,
                    'jumlah' => (int) $x->jumlah,
                    'panjang_m' => (float) $x->panjang_m,
                    'lebar_m' => (float) $x->lebar_m,
                    'tebal_m' => (float) $x->tebal_m,
                    'volume_m3' => (float) $x->volume_m3,
                    'keterangan' => $x->keterangan,
                ])->values(),
            ],
        ]);
    }
    private function buildPdfData(Request $request, BuildingEstimation $buildingEstimation): array
    {
        $buildingEstimation->load(['items', 'deductions']);

        $project = [
            'nama_proyek' => $buildingEstimation->nama_proyek,
            'lokasi_proyek' => $buildingEstimation->lokasi_proyek,
            'mutu_beton' => $buildingEstimation->mutu_beton,
            'waste_percent' => (float) $buildingEstimation->waste_percent,
            'metode_input' => 'Bangunan (Item Struktur)',
            'tanggal_estimasi' => optional($buildingEstimation->created_at)->format('Y-m-d H:i:s'),
        ];

        $parameters = [
            ['label' => 'Jumlah Lantai', 'value' => (int) $buildingEstimation->jumlah_lantai],
        ];

        $detailItems = $buildingEstimation->items->map(fn ($x) => [
            'nama' => trim(($x->jenis_item ?? '-') . ' - ' . ($x->nama_item ?? '-')),
            'jumlah' => (int) $x->jumlah,
            'dimensi' => number_format((float)$x->panjang_m, 3) . ' × ' . number_format((float)$x->lebar_m, 3) . ' × ' . number_format((float)$x->tebal_m, 3) . ' m',
            'volume' => number_format((float)$x->volume_m3, 4),
            'keterangan' => $x->keterangan ?: '-',
        ])->values()->all();

        $deductions = $buildingEstimation->deductions->map(fn ($x) => [
            'nama' => $x->jenis_pengurang ?: '-',
            'jumlah' => (int) $x->jumlah,
            'dimensi' => number_format((float)$x->panjang_m, 3) . ' × ' . number_format((float)$x->lebar_m, 3) . ' × ' . number_format((float)$x->tebal_m, 3) . ' m',
            'volume' => number_format((float)$x->volume_m3, 4),
            'keterangan' => $x->keterangan ?: '-',
        ])->values()->all();

        $summary = [
            'volume_kotor' => number_format((float)$buildingEstimation->volume_kotor, 4, ',', '.'),
            'volume_pengurang' => number_format((float)$buildingEstimation->volume_pengurang, 4, ',', '.'),
            'volume_bersih' => number_format((float)$buildingEstimation->volume_bersih, 4, ',', '.'),
            'waste_volume' => number_format((float)$buildingEstimation->waste_volume, 4, ',', '.'),
            'total_akhir_m3' => number_format((float)$buildingEstimation->total_akhir_m3, 4, ',', '.'),
            'harga_per_m3' => number_format((float)$buildingEstimation->harga_per_m3, 0, ',', '.'),
            'estimasi_harga_total' => number_format((float)$buildingEstimation->estimasi_harga_total, 0, ',', '.'),
        ];

        return [
            'printed_at' => now()->format('Y-m-d H:i:s'),
            'jenis_kalkulasi' => 'Cor Rumah/Gedung',
            'document_no' => 'EST-' . str_pad((string)$buildingEstimation->id, 6, '0', STR_PAD_LEFT),
            'prepared_by' => $request->user()->name ?? $request->user()->email,
            'app_name' => config('app.name'),

            'project' => $project,
            'parameters' => $parameters,
            'detail_items' => $detailItems,
            'deductions' => $deductions,
            'summary' => $summary,
        ];
    }

        public function previewPdf(Request $request, BuildingEstimation $buildingEstimation)
    {
        abort_if($buildingEstimation->user_id !== $request->user()->id, 403);

        $data = $this->buildPdfData($request, $buildingEstimation);

        return view('pdf.preview-building', [
            'buildingEstimation' => $buildingEstimation,
            'data' => $data,
        ]);
    }

    public function exportPdf(Request $request, BuildingEstimation $buildingEstimation)
    {
        abort_if($buildingEstimation->user_id !== $request->user()->id, 403);

        $data = $this->buildPdfData($request, $buildingEstimation);

        $pdf = Pdf::loadView('pdf.estimasi-template', $data)->setPaper('a4', 'portrait');

        $safeName = Str::slug($buildingEstimation->nama_proyek ?: 'proyek');
        $filename = "estimasi-building-{$buildingEstimation->id}-{$safeName}.pdf";

        return $pdf->download($filename);
    }
}