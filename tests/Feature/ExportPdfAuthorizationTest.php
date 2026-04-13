<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BuildingEstimation;
use App\Models\RoadEstimation;
use App\Models\QuickCastEstimation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExportPdfAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'engineer']);
    }

    private function engineerUser(string $email): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole('engineer');
        return $user;
    }

    private function createBuilding(User $owner): BuildingEstimation
    {
        return BuildingEstimation::query()->create([
            'user_id' => $owner->id,
            'nama_proyek' => 'Proyek Building Test',
            'lokasi_proyek' => 'Jakarta',
            'mutu_beton' => 'K-250',
            'waste_percent' => 5,
            'jumlah_lantai' => 1,
            'volume_kotor' => 10,
            'volume_pengurang' => 0,
            'volume_bersih' => 10,
            'waste_volume' => 0.5,
            'total_akhir_m3' => 10.5,
            'harga_per_m3' => 900000,
            'estimasi_harga_total' => 9450000,
        ]);
    }

    private function createRoad(User $owner): RoadEstimation
    {
        return RoadEstimation::query()->create([
            'user_id' => $owner->id,
            'nama_proyek' => 'Proyek Road Test',
            'lokasi_proyek' => 'Bandung',
            'metode_input' => 'total',
            'jumlah_lajur' => 2,
            'lebar_per_lajur_m' => 3.5,
            'bahu_kiri_m' => 1,
            'bahu_kanan_m' => 1,
            'lebar_total_m' => 9,
            'tebal_beton_m' => 0.2,
            'panjang_total_m' => 100,
            'mutu_beton' => 'K-300',
            'waste_percent' => 5,
            'volume_kotor' => 180,
            'volume_pengurang' => 0,
            'volume_bersih' => 180,
            'waste_volume' => 9,
            'total_akhir_m3' => 189,
            'harga_per_m3' => 1000000,
            'estimasi_harga_total' => 189000000,
        ]);
    }

    private function createQuick(User $owner): QuickCastEstimation
    {
        return QuickCastEstimation::query()->create([
            'user_id' => $owner->id,
            'nama_proyek' => 'Proyek Quick Test',
            'lokasi_proyek' => 'Surabaya',
            'panjang_m' => 10,
            'lebar_m' => 5,
            'tebal_cm' => 12,
            'tebal_m' => 0.12,
            'beban_penggunaan' => 'sedang',
            'mutu_rekomendasi' => 'K-250',
            'waste_percent' => 5,
            'volume_kotor' => 6,
            'volume_bersih' => 6,
            'waste_volume' => 0.3,
            'total_akhir_m3' => 6.3,
            'harga_per_m3' => 950000,
            'estimasi_harga_total' => 5985000,
        ]);
    }

    public function test_building_export_pdf_owner_can_access(): void
    {
        $owner = $this->engineerUser('owner-building@test.com');
        $estimation = $this->createBuilding($owner);

        $this->actingAs($owner)
            ->get(route('engineer.building.export-pdf', $estimation->id))
            ->assertOk();
    }

    public function test_building_export_pdf_non_owner_forbidden(): void
    {
        $owner = $this->engineerUser('owner-building2@test.com');
        $other = $this->engineerUser('other-building@test.com');
        $estimation = $this->createBuilding($owner);

        $this->actingAs($other)
            ->get(route('engineer.building.export-pdf', $estimation->id))
            ->assertForbidden();
    }

    public function test_road_export_pdf_owner_can_access(): void
    {
        $owner = $this->engineerUser('owner-road@test.com');
        $estimation = $this->createRoad($owner);

        $this->actingAs($owner)
            ->get(route('engineer.road.export-pdf', $estimation->id))
            ->assertOk();
    }

    public function test_road_export_pdf_non_owner_forbidden(): void
    {
        $owner = $this->engineerUser('owner-road2@test.com');
        $other = $this->engineerUser('other-road@test.com');
        $estimation = $this->createRoad($owner);

        $this->actingAs($other)
            ->get(route('engineer.road.export-pdf', $estimation->id))
            ->assertForbidden();
    }

    public function test_quickcast_export_pdf_owner_can_access(): void
    {
        $owner = $this->engineerUser('owner-quick@test.com');
        $estimation = $this->createQuick($owner);

        $this->actingAs($owner)
            ->get(route('engineer.quick-cast.export-pdf', $estimation->id))
            ->assertOk();
    }

    public function test_quickcast_export_pdf_non_owner_forbidden(): void
    {
        $owner = $this->engineerUser('owner-quick2@test.com');
        $other = $this->engineerUser('other-quick@test.com');
        $estimation = $this->createQuick($owner);

        $this->actingAs($other)
            ->get(route('engineer.quick-cast.export-pdf', $estimation->id))
            ->assertForbidden();
    }
}