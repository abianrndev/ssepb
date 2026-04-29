<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuildingEstimation;
use App\Models\QuickCastEstimation;
use App\Models\RoadEstimation;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalUsers = User::query()->count('*');
        $activeEngineers = User::query()->role('engineer')->where('is_active', true)->count('*');

        $quickCount = QuickCastEstimation::query()->count('*');
        $roadCount = RoadEstimation::query()->count('*');
        $buildingCount = BuildingEstimation::query()->count('*');

        $totalEstimations = $quickCount + $roadCount + $buildingCount;

        $recentQuick = QuickCastEstimation::query()
            ->with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($x) => [
                'module' => 'Quick Cast',
                'nama_proyek' => $x->nama_proyek ?? '-',
                'user' => $x->user?->name ?? '-',
                'created_at' => optional($x->created_at)->format('Y-m-d H:i'),
            ]);

        $recentRoad = RoadEstimation::query()
            ->with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($x) => [
                'module' => 'Road',
                'nama_proyek' => $x->nama_proyek ?? '-',
                'user' => $x->user?->name ?? '-',
                'created_at' => optional($x->created_at)->format('Y-m-d H:i'),
            ]);

        $recentBuilding = BuildingEstimation::query()
            ->with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($x) => [
                'module' => 'Building',
                'nama_proyek' => $x->nama_proyek ?? '-',
                'user' => $x->user?->name ?? '-',
                'created_at' => optional($x->created_at)->format('Y-m-d H:i'),
            ]);

        $recent = $recentQuick
            ->merge($recentRoad)
            ->merge($recentBuilding)
            ->sortByDesc('created_at')
            ->take(5)
            ->values()
            ->all();

        $trend = collect(range(6, 0))
            ->map(function ($i) {
                $date = Carbon::today()->subDays($i)->format('Y-m-d');

                $total = QuickCastEstimation::query()->whereDate('created_at', '=', $date, 'and')->count('*')
                    + RoadEstimation::query()->whereDate('created_at', '=', $date, 'and')->count('*')
                    + BuildingEstimation::query()->whereDate('created_at', '=', $date, 'and')->count('*');

                return [
                    'date' => $date,
                    'total' => $total,
                ];
            });

        $topUsers = User::query()
            ->select(['id', 'name'])
            ->get()
            ->map(function ($user) {
                $count = QuickCastEstimation::query()->where('user_id', $user->id)->count('*')
                    + RoadEstimation::query()->where('user_id', $user->id)->count('*')
                    + BuildingEstimation::query()->where('user_id', $user->id)->count('*');

                return [
                    'name' => $user->name,
                    'total' => $count,
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->all();

        return Inertia::render('Admin/Index', [
            'stats' => [
                'total_users' => $totalUsers,
                'active_engineers' => $activeEngineers,
                'total_estimations' => $totalEstimations,
                'quick_count' => $quickCount,
                'road_count' => $roadCount,
                'building_count' => $buildingCount,
            ],
            'recent_estimations' => $recent,
            'trend' => $trend,
            'top_users' => $topUsers,
        ]);
    }
}