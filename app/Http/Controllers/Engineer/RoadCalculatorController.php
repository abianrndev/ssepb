<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\PriceSetting;
use App\Models\RoadDeduction;
use App\Models\RoadEstimation;
use App\Models\BuildingEstimation;
use App\Models\RoadSegment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class RoadCalculatorController extends Controller
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

        return Inertia::render('Engineer/Road/RoadCalculator', [
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
            'metode_input' => ['required', 'in:total,segmen'],
            'jumlah_lajur' => ['required', 'integer', 'min:1'],
            'lebar_per_lajur_m' => ['required', 'numeric', 'gt:0'],
            'bahu_kiri_m' => ['nullable', 'numeric', 'gte:0'],
            'bahu_kanan_m' => ['nullable', 'numeric', 'gte:0'],
            'tebal_beton_m' => ['required', 'numeric', 'gt:0'],
            'panjang_total_m' => ['nullable', 'numeric', 'gt:0'],
            'mutu_beton' => ['required', 'string', 'max:50'],
            'waste_percent' => ['required', 'numeric', 'gte:0', 'lte:50'],

            'segments' => ['nullable', 'array'],
            'segments.*.sta_awal' => ['nullable', 'string', 'max:50'],
            'segments.*.sta_akhir' => ['nullable', 'string', 'max:50'],
            'segments.*.panjang_m' => ['required_if:metode_input,segmen', 'nullable', 'numeric', 'gt:0'],
            'segments.*.lebar_m' => ['nullable', 'numeric', 'gt:0'],
            'segments.*.tebal_m' => ['nullable', 'numeric', 'gt:0'],
            'segments.*.keterangan' => ['nullable', 'string', 'max:255'],

            'deductions' => ['nullable', 'array'],
            'deductions.*.jenis_bukaan' => ['required_with:deductions.*.panjang_m', 'nullable', 'string', 'max:100'],
            'deductions.*.panjang_m' => ['nullable', 'numeric', 'gt:0'],
            'deductions.*.lebar_m' => ['nullable', 'numeric', 'gt:0'],
            'deductions.*.jumlah' => ['nullable', 'integer', 'min:1'],
        ]);

        $bahuKiri = (float) ($validated['bahu_kiri_m'] ?? 0);
        $bahuKanan = (float) ($validated['bahu_kanan_m'] ?? 0);
        $lebarTotal = ((int) $validated['jumlah_lajur'] * (float) $validated['lebar_per_lajur_m']) + $bahuKiri + $bahuKanan;
        $tebalDefault = (float) $validated['tebal_beton_m'];

        $volumeKotor = 0.0;
        $segmentRows = [];

        if ($validated['metode_input'] === 'total') {
            $panjangTotal = (float) ($validated['panjang_total_m'] ?? 0);
            if ($panjangTotal <= 0) {
                return back()->withErrors(['panjang_total_m' => 'Panjang total wajib diisi untuk mode total.']);
            }
            $volumeKotor = $panjangTotal * $lebarTotal * $tebalDefault;
        } else {
            $segments = $validated['segments'] ?? [];
            if (count($segments) === 0) {
                return back()->withErrors(['segments' => 'Minimal 1 segmen diperlukan untuk mode segmen.']);
            }

            foreach ($segments as $seg) {
                $p = (float) ($seg['panjang_m'] ?? 0);
                if ($p <= 0) continue;

                $l = (float) ($seg['lebar_m'] ?? $lebarTotal);
                $t = (float) ($seg['tebal_m'] ?? $tebalDefault);
                $v = $p * $l * $t;

                $volumeKotor += $v;

                $segmentRows[] = [
                    'sta_awal' => $seg['sta_awal'] ?? null,
                    'sta_akhir' => $seg['sta_akhir'] ?? null,
                    'panjang_m' => $p,
                    'lebar_m' => $l,
                    'tebal_m' => $t,
                    'keterangan' => $seg['keterangan'] ?? null,
                    'volume_m3' => $v,
                ];
            }

            if ($volumeKotor <= 0) {
                return back()->withErrors(['segments' => 'Segmen tidak valid. Cek panjang/lebar/tebal.']);
            }
        }

        $deductionRows = [];
        $volumePengurang = 0.0;
        foreach (($validated['deductions'] ?? []) as $d) {
            $jenis = $d['jenis_bukaan'] ?? null;
            $p = (float) ($d['panjang_m'] ?? 0);
            $l = (float) ($d['lebar_m'] ?? 0);
            $j = (int) ($d['jumlah'] ?? 1);

            if (!$jenis || $p <= 0 || $l <= 0 || $j <= 0) continue;

            $v = $p * $l * $tebalDefault * $j;
            $volumePengurang += $v;

            $deductionRows[] = [
                'jenis_bukaan' => $jenis,
                'panjang_m' => $p,
                'lebar_m' => $l,
                'jumlah' => $j,
                'volume_m3' => $v,
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

        DB::transaction(function () use ($request, $validated, $lebarTotal, $tebalDefault, $volumeKotor, $volumePengurang, $volumeBersih, $wasteVolume, $totalAkhir, $hargaPerM3, $estimasiHargaTotal, $segmentRows, $deductionRows) {
            $road = RoadEstimation::create([
                'user_id' => $request->user()->id,
                'nama_proyek' => $validated['nama_proyek'] ?? null,
                'lokasi_proyek' => $validated['lokasi_proyek'] ?? null,
                'metode_input' => $validated['metode_input'],
                'jumlah_lajur' => $validated['jumlah_lajur'],
                'lebar_per_lajur_m' => $validated['lebar_per_lajur_m'],
                'bahu_kiri_m' => $validated['bahu_kiri_m'] ?? 0,
                'bahu_kanan_m' => $validated['bahu_kanan_m'] ?? 0,
                'tebal_beton_m' => $tebalDefault,
                'lebar_total_m' => $lebarTotal,
                'panjang_total_m' => $validated['metode_input'] === 'total' ? $validated['panjang_total_m'] : null,
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

            foreach ($segmentRows as $row) {
                $row['road_estimation_id'] = $road->id;
                RoadSegment::create($row);
            }

            foreach ($deductionRows as $row) {
                $row['road_estimation_id'] = $road->id;
                RoadDeduction::create($row);
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

        return redirect()->route('engineer.road.form', [
            'success' => 'Estimasi beton jalan berhasil dihitung & disimpan.',
            'result' => base64_encode(json_encode($result)),
        ]);
    }

    public function history(Request $request): Response
    {
        $query = RoadEstimation::query()
            ->where('user_id', $request->user()->id);

        if ($request->filled('metode_input') && in_array($request->metode_input, ['total', 'segmen'])) {
            $query->where('metode_input', $request->metode_input);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $rows = $query->latest()->get()->map(fn ($r) => [
            'id' => $r->id,
            'created_at' => optional($r->created_at)->format('Y-m-d H:i:s'),
            'nama_proyek' => $r->nama_proyek,
            'lokasi_proyek' => $r->lokasi_proyek,
            'metode_input' => $r->metode_input,
            'mutu_beton' => $r->mutu_beton,
            'total_akhir_m3' => (float) $r->total_akhir_m3,
            'estimasi_harga_total' => (float) $r->estimasi_harga_total,
        ])->values();

        return Inertia::render('Engineer/Road/RoadHistory', [
            'histories' => $rows,
            'filters' => [
                'metode_input' => $request->get('metode_input', ''),
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', ''),
            ],
        ]);
    }

    public function detail(Request $request, RoadEstimation $roadEstimation): Response
    {
        abort_if($roadEstimation->user_id !== $request->user()->id, 403);

        $roadEstimation->load(['segments', 'deductions']);

        return Inertia::render('Engineer/Road/RoadDetail', [
            'item' => [
                'id' => $roadEstimation->id,
                'created_at' => optional($roadEstimation->created_at)->format('Y-m-d H:i:s'),
                'nama_proyek' => $roadEstimation->nama_proyek,
                'lokasi_proyek' => $roadEstimation->lokasi_proyek,

                'metode_input' => $roadEstimation->metode_input,
                'jumlah_lajur' => (int) $roadEstimation->jumlah_lajur,
                'lebar_per_lajur_m' => (float) $roadEstimation->lebar_per_lajur_m,
                'bahu_kiri_m' => (float) $roadEstimation->bahu_kiri_m,
                'bahu_kanan_m' => (float) $roadEstimation->bahu_kanan_m,
                'tebal_beton_m' => (float) $roadEstimation->tebal_beton_m,
                'lebar_total_m' => (float) $roadEstimation->lebar_total_m,
                'panjang_total_m' => $roadEstimation->panjang_total_m !== null ? (float) $roadEstimation->panjang_total_m : null,

                'mutu_beton' => $roadEstimation->mutu_beton,
                'waste_percent' => (float) $roadEstimation->waste_percent,

                'volume_kotor' => (float) $roadEstimation->volume_kotor,
                'volume_pengurang' => (float) $roadEstimation->volume_pengurang,
                'volume_bersih' => (float) $roadEstimation->volume_bersih,
                'waste_volume' => (float) $roadEstimation->waste_volume,
                'total_akhir_m3' => (float) $roadEstimation->total_akhir_m3,

                'harga_per_m3' => (float) $roadEstimation->harga_per_m3,
                'estimasi_harga_total' => (float) $roadEstimation->estimasi_harga_total,

                'segments' => $roadEstimation->segments->map(fn ($s) => [
                    'id' => $s->id,
                    'sta_awal' => $s->sta_awal,
                    'sta_akhir' => $s->sta_akhir,
                    'panjang_m' => (float) $s->panjang_m,
                    'lebar_m' => (float) $s->lebar_m,
                    'tebal_m' => (float) $s->tebal_m,
                    'keterangan' => $s->keterangan,
                    'volume_m3' => (float) $s->volume_m3,
                ])->values(),

                'deductions' => $roadEstimation->deductions->map(fn ($d) => [
                    'id' => $d->id,
                    'jenis_bukaan' => $d->jenis_bukaan,
                    'panjang_m' => (float) $d->panjang_m,
                    'lebar_m' => (float) $d->lebar_m,
                    'jumlah' => (int) $d->jumlah,
                    'volume_m3' => (float) $d->volume_m3,
                ])->values(),
            ],
        ]);
    }
    private function buildPdfData(Request $request, RoadEstimation $roadEstimation): array
    {
        return [
            'printed_at' => now()->format('Y-m-d H:i:s'),
            'document_no' => 'EST-ROAD-' . str_pad((string)$roadEstimation->id, 6, '0', STR_PAD_LEFT),
            'prepared_by' => $request->user()->name ?? $request->user()->email,
            'app_name' => config('app.name'),
            'project' => [
                'nama_proyek' => $roadEstimation->nama_proyek ?? '-',
                'lokasi_proyek' => $roadEstimation->lokasi_proyek ?? '-',
                'mutu_beton' => $roadEstimation->mutu_beton ?? '-',
                'tanggal_estimasi' => optional($roadEstimation->created_at)->format('Y-m-d H:i:s'),
            ],
            'summary' => [
                'volume_kotor' => number_format((float)($roadEstimation->volume_kotor ?? 0), 4, ',', '.'),
                'volume_pengurang' => number_format((float)($roadEstimation->volume_pengurang ?? 0), 4, ',', '.'),
                'volume_bersih' => number_format((float)($roadEstimation->volume_bersih ?? 0), 4, ',', '.'),
                'waste_volume' => number_format((float)($roadEstimation->waste_volume ?? 0), 4, ',', '.'),
                'total_akhir_m3' => number_format((float)($roadEstimation->total_akhir_m3 ?? 0), 4, ',', '.'),
                'harga_per_m3' => number_format((float)($roadEstimation->harga_per_m3 ?? 0), 0, ',', '.'),
                'estimasi_harga_total' => number_format((float)($roadEstimation->estimasi_harga_total ?? 0), 0, ',', '.'),
            ],
        ];
    }

    public function exportPdf(Request $request, RoadEstimation $roadEstimation)
    {
        abort_if($roadEstimation->user_id !== $request->user()->id, 403);

        $data = $this->buildPdfData($request, $roadEstimation);
        $pdf = Pdf::loadView('pdf.estimasi-road-template', $data)->setPaper('a4', 'portrait');

        $safeName = Str::slug($roadEstimation->nama_proyek ?: 'proyek-jalan');
        return $pdf->download("estimasi-road-{$roadEstimation->id}-{$safeName}.pdf");
    }
}