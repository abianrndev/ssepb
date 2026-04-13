<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\PriceSetting;
use App\Models\QuickCastEstimation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class QuickCastController extends Controller
{
    public function showCalculator(Request $request): Response
    {
        $result = null;

        if ($request->filled('result')) {
            $decoded = json_decode(base64_decode($request->query('result')), true);
            if (is_array($decoded)) {
                $result = $decoded;
            }
        }

        return Inertia::render('Engineer/QuickCast/QuickCastCalculator', [
            'result' => $result,
            'success' => $request->query('success'),
        ]);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'nama_proyek' => ['nullable', 'string', 'max:255'],
            'lokasi_proyek' => ['nullable', 'string', 'max:255'],
            'panjang_m' => ['required', 'numeric', 'gt:0'],
            'lebar_m' => ['required', 'numeric', 'gt:0'],
            'tebal_cm' => ['required', 'numeric', 'gt:0'],
            'beban_penggunaan' => ['required', 'in:ringan,sedang,berat'],
            'waste_percent' => ['nullable', 'numeric', 'gte:0', 'lte:50'],
        ]);

        $wastePercent = isset($validated['waste_percent']) ? (float) $validated['waste_percent'] : 5.0;
        $tebalM = (float) $validated['tebal_cm'] / 100;

        $volumeKotor = (float) $validated['panjang_m'] * (float) $validated['lebar_m'] * $tebalM;
        $volumeBersih = $volumeKotor;
        $wasteVolume = $volumeBersih * ($wastePercent / 100);
        $totalAkhir = $volumeBersih + $wasteVolume;

        $mutuRekomendasi = $this->mapBebanToMutu($validated['beban_penggunaan']);
        $hargaPerM3 = $this->getHargaAktifByMutu($mutuRekomendasi);
        $estimasiTotal = $totalAkhir * $hargaPerM3;

        QuickCastEstimation::create([
            'user_id' => $request->user()->id,
            'nama_proyek' => $validated['nama_proyek'] ?? null,
            'lokasi_proyek' => $validated['lokasi_proyek'] ?? null,
            'panjang_m' => $validated['panjang_m'],
            'lebar_m' => $validated['lebar_m'],
            'tebal_cm' => $validated['tebal_cm'],
            'tebal_m' => $tebalM,
            'beban_penggunaan' => $validated['beban_penggunaan'],
            'mutu_rekomendasi' => $mutuRekomendasi,
            'waste_percent' => $wastePercent,
            'volume_kotor' => $volumeKotor,
            'volume_bersih' => $volumeBersih,
            'waste_volume' => $wasteVolume,
            'total_akhir_m3' => $totalAkhir,
            'harga_per_m3' => $hargaPerM3,
            'estimasi_harga_total' => $estimasiTotal,
        ]);

        $result = [
            'mutu_rekomendasi' => $mutuRekomendasi,
            'harga_per_m3' => round($hargaPerM3, 2),
            'volume_kotor' => round($volumeKotor, 4),
            'volume_bersih' => round($volumeBersih, 4),
            'waste_volume' => round($wasteVolume, 4),
            'total_akhir_m3' => round($totalAkhir, 4),
            'estimasi_harga_total' => round($estimasiTotal, 2),
        ];

        return redirect()->route('engineer.quick-cast.form', [
            'success' => 'Estimasi berhasil dihitung & disimpan.',
            'result' => base64_encode(json_encode($result)),
        ]);
    }

    public function history(Request $request): Response
    {
        $rows = QuickCastEstimation::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'nama_proyek' => $r->nama_proyek,
                'lokasi_proyek' => $r->lokasi_proyek,
                'mutu_rekomendasi' => $r->mutu_rekomendasi,
                'total_akhir_m3' => (float) $r->total_akhir_m3,
                'estimasi_harga_total' => (float) $r->estimasi_harga_total,
                'created_at' => optional($r->created_at)->format('Y-m-d H:i:s'),
            ])
            ->values();

        return Inertia::render('Engineer/QuickCast/QuickCastHistory', [
            'histories' => $rows,
        ]);
    }

    public function detail(Request $request, QuickCastEstimation $quickCastEstimation): Response
    {
        abort_if($quickCastEstimation->user_id !== $request->user()->id, 403);

        return Inertia::render('Engineer/QuickCast/QuickCastDetail', [
            'item' => [
                'id' => $quickCastEstimation->id,
                'nama_proyek' => $quickCastEstimation->nama_proyek,
                'lokasi_proyek' => $quickCastEstimation->lokasi_proyek,
                'panjang_m' => (float) $quickCastEstimation->panjang_m,
                'lebar_m' => (float) $quickCastEstimation->lebar_m,
                'tebal_cm' => (float) $quickCastEstimation->tebal_cm,
                'tebal_m' => (float) $quickCastEstimation->tebal_m,
                'beban_penggunaan' => $quickCastEstimation->beban_penggunaan,
                'mutu_rekomendasi' => $quickCastEstimation->mutu_rekomendasi,
                'waste_percent' => (float) $quickCastEstimation->waste_percent,
                'volume_kotor' => (float) $quickCastEstimation->volume_kotor,
                'volume_bersih' => (float) $quickCastEstimation->volume_bersih,
                'waste_volume' => (float) $quickCastEstimation->waste_volume,
                'total_akhir_m3' => (float) $quickCastEstimation->total_akhir_m3,
                'harga_per_m3' => (float) $quickCastEstimation->harga_per_m3,
                'estimasi_harga_total' => (float) $quickCastEstimation->estimasi_harga_total,
                'created_at' => optional($quickCastEstimation->created_at)->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    private function mapBebanToMutu(string $beban): string
    {
        return match ($beban) {
            'ringan' => 'K-200',
            'sedang' => 'K-250',
            'berat' => 'K-300',
            default => 'K-250',
        };
    }

    private function getHargaAktifByMutu(string $mutu): float
    {
        $price = PriceSetting::query()
            ->where('mutu_beton', $mutu)
            ->where('is_active', true)
            ->first();

        return $price ? (float) $price->harga_per_m3 : 0.0;
    }

    private function buildPdfData(Request $request, QuickCastEstimation $quickCastEstimation): array
        {
            $tebalCm = (float) $quickCastEstimation->tebal_cm;
            $tebalM  = (float) $quickCastEstimation->tebal_m;
            $panjang = (float) $quickCastEstimation->panjang_m;
            $lebar   = (float) $quickCastEstimation->lebar_m;

            $project = [
                'nama_proyek'       => $quickCastEstimation->nama_proyek ?? '-',
                'lokasi_proyek'     => $quickCastEstimation->lokasi_proyek ?? '-',
                'mutu_beton'        => $quickCastEstimation->mutu_rekomendasi ?? '-',
                'waste_percent'     => (float) $quickCastEstimation->waste_percent,
                'metode_input'      => 'Quick Cast (Cor Cepat)',
                'tanggal_estimasi'  => optional($quickCastEstimation->created_at)->format('Y-m-d H:i:s'),
            ];

            $parameters = [
                ['label' => 'Panjang',           'value' => number_format($panjang, 2, ',', '.') . ' m'],
                ['label' => 'Lebar',             'value' => number_format($lebar, 2, ',', '.') . ' m'],
                ['label' => 'Tebal',             'value' => number_format($tebalCm, 2, ',', '.') . ' cm (' . number_format($tebalM, 4, ',', '.') . ' m)'],
                ['label' => 'Beban Penggunaan',  'value' => ucfirst($quickCastEstimation->beban_penggunaan ?? '-')],
                ['label' => 'Mutu Rekomendasi',  'value' => $quickCastEstimation->mutu_rekomendasi ?? '-'],
            ];

            $detailItems = [
                [
                    'nama'        => 'Area Cor (' . number_format($panjang, 2) . ' × ' . number_format($lebar, 2) . ' m)',
                    'jumlah'      => 1,
                    'dimensi'     => number_format($panjang, 3) . ' × ' . number_format($lebar, 3) . ' × ' . number_format($tebalM, 3) . ' m',
                    'volume'      => number_format((float) $quickCastEstimation->volume_kotor, 4),
                    'keterangan'  => 'Beban: ' . ucfirst($quickCastEstimation->beban_penggunaan ?? '-'),
                ],
            ];

            $summary = [
                'volume_kotor'          => number_format((float) $quickCastEstimation->volume_kotor, 4, ',', '.'),
                'volume_pengurang'      => number_format(0, 4, ',', '.'),
                'volume_bersih'         => number_format((float) $quickCastEstimation->volume_bersih, 4, ',', '.'),
                'waste_volume'          => number_format((float) $quickCastEstimation->waste_volume, 4, ',', '.'),
                'total_akhir_m3'        => number_format((float) $quickCastEstimation->total_akhir_m3, 4, ',', '.'),
                'harga_per_m3'          => number_format((float) $quickCastEstimation->harga_per_m3, 0, ',', '.'),
                'estimasi_harga_total'  => number_format((float) $quickCastEstimation->estimasi_harga_total, 0, ',', '.'),
            ];

            return [
                'printed_at'        => now()->format('Y-m-d H:i:s'),
                'jenis_kalkulasi'   => 'Cor Cepat (Quick Cast)',
                'document_no'       => 'EST-QC-' . str_pad((string) $quickCastEstimation->id, 6, '0', STR_PAD_LEFT),
                'prepared_by'       => $request->user()->name ?? $request->user()->email,
                'app_name'          => config('app.name'),

                'project'       => $project,
                'parameters'    => $parameters,
                'detail_items'  => $detailItems,
                'deductions'    => [],
                'summary'       => $summary,
            ];
        }

        public function exportPdf(Request $request, QuickCastEstimation $quickCastEstimation)
        {
            abort_if($quickCastEstimation->user_id !== $request->user()->id, 403);
        
            $data = $this->buildPdfData($request, $quickCastEstimation);
        
            $pdf = Pdf::loadView('pdf.estimasi-template', $data)->setPaper('a4', 'portrait');
        
            $safeName = Str::slug($quickCastEstimation->nama_proyek ?: 'proyek-quick-cast');
            $filename = "estimasi-quickcast-{$quickCastEstimation->id}-{$safeName}.pdf";
        
            return $pdf->download($filename);
        }
}