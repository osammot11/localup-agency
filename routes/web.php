<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\GoogleSheetsController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\AntiquesLandingController;
use App\Http\Controllers\BeautyLandingController;
use App\Http\Controllers\MarketingConsentController;
use App\Http\Controllers\PastryLandingController;
use App\Http\Controllers\RestaurantLandingController;
use App\Http\Controllers\WebsiteLandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/social/ristoranti', [RestaurantLandingController::class, 'show'])->name('restaurants.landing');
Route::post('/social/ristoranti', [RestaurantLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('restaurants.lead');

Route::get('/social/beauty', [BeautyLandingController::class, 'show'])->name('beauty.landing');
Route::post('/social/beauty', [BeautyLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('beauty.lead');

Route::get('/social/antiquariato', [AntiquesLandingController::class, 'show'])->name('antiques.landing');
Route::post('/social/antiquariato', [AntiquesLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('antiques.lead');

Route::get('/social/pasticcerie', [PastryLandingController::class, 'show'])->name('pastry.landing');
Route::post('/social/pasticcerie', [PastryLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('pastry.lead');

Route::get('/siti', [WebsiteLandingController::class, 'show'])->name('sites.landing');
Route::post('/siti', [WebsiteLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('sites.lead');

Route::post('/tracking/consent', [MarketingConsentController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('tracking.consent');

Route::get('/admin/login', [AuthController::class, 'show'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('admin.login');

Route::middleware(['auth', 'can:view-admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::get('/google-sheets', [GoogleSheetsController::class, 'show'])->name('google-sheets.show');
    Route::post('/google-sheets', [GoogleSheetsController::class, 'update'])->name('google-sheets.update');
    Route::post('/google-sheets/sync', [GoogleSheetsController::class, 'sync'])
        ->middleware('throttle:3,1')->name('google-sheets.sync');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
