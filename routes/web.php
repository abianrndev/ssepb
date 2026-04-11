<?php

use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Engineer\QuickCastController;
use App\Http\Controllers\Engineer\RoadCalculatorController;
use App\Http\Controllers\Engineer\BuildingCalculatorController;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    $user = request()->user();

    if ($user?->hasRole('admin')) return redirect('/admin');
    if ($user?->hasRole('engineer')) return redirect('/engineer');

    abort(403, 'Role tidak dikenali.');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', RoleMiddleware::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => Inertia::render('Admin/Index'))->name('index');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
    Route::patch('/pricing/{priceSetting}', [PricingController::class, 'update'])->name('pricing.update');
});

Route::middleware(['auth', 'role:engineer'])->prefix('engineer')->name('engineer.')->group(function () {
    Route::get('/', fn () => Inertia::render('Engineer/Index'))->name('index');

    Route::get('/quick-cast', [QuickCastController::class, 'showCalculator'])->name('quick-cast.form');
    Route::post('/quick-cast', [QuickCastController::class, 'calculate'])->name('quick-cast.calculate');
    Route::get('/quick-cast/history', [QuickCastController::class, 'history'])->name('quick-cast.history');
    Route::get('/quick-cast/history/{quickCastEstimation}', [QuickCastController::class, 'detail'])->whereNumber('quickCastEstimation')->name('quick-cast.detail');
    Route::get('/quick-cast/history/{quickCastEstimation}/export-pdf', [QuickCastController::class, 'exportPdf'])->whereNumber('quickCastEstimation')->name('quick-cast.export-pdf');

    Route::get('/road', [RoadCalculatorController::class, 'showCalculator'])->name('road.form');
    Route::post('/road', [RoadCalculatorController::class, 'calculate'])->name('road.calculate');
    Route::get('/road/history', [RoadCalculatorController::class, 'history'])->name('road.history');
    Route::get('/road/history/{roadEstimation}', [RoadCalculatorController::class, 'detail'])->whereNumber('roadEstimation')->name('road.detail');
    Route::get('/road/history/{roadEstimation}/export-pdf', [RoadCalculatorController::class, 'exportPdf'])->whereNumber('roadEstimation')->name('road.export-pdf');

    Route::get('/building', [BuildingCalculatorController::class, 'showCalculator'])->name('building.form');
    Route::post('/building', [BuildingCalculatorController::class, 'calculate'])->name('building.calculate');
    Route::get('/building/history', [BuildingCalculatorController::class, 'history'])->name('building.history');
    Route::get('/building/history/{buildingEstimation}', [BuildingCalculatorController::class, 'detail'])->whereNumber('buildingEstimation')->name('building.detail');
    Route::get('/building/history/{buildingEstimation}/export-pdf', [BuildingCalculatorController::class, 'exportPdf'])->whereNumber('buildingEstimation')->name('building.export-pdf');
});

require __DIR__ . '/auth.php';