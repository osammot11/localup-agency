<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\GoogleSheetsController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\AntiquesLandingController;
use App\Http\Controllers\BeautyLandingController;
use App\Http\Controllers\FormProgressTrackingController;
use App\Http\Controllers\MarketingConsentController;
use App\Http\Controllers\PastryLandingController;
use App\Http\Controllers\RestaurantLandingController;
use App\Http\Controllers\RestaurantTrialLandingController;
use App\Http\Controllers\TrialLandingController;
use App\Http\Controllers\WebsiteLandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/social/ristoranti-v2', [RestaurantLandingController::class, 'show'])->name('restaurants.landing');
Route::post('/social/ristoranti-v2', [RestaurantLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('restaurants.lead');

Route::get('/social/ristoranti', [RestaurantTrialLandingController::class, 'show'])->name('restaurants-trial.landing');
Route::post('/social/ristoranti', [RestaurantTrialLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('restaurants-trial.lead');

Route::get('/social/beauty-v2', [BeautyLandingController::class, 'show'])->name('beauty.landing');
Route::post('/social/beauty-v2', [BeautyLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('beauty.lead');

Route::get('/social/beauty', [TrialLandingController::class, 'show'])->defaults('variant', 'beauty')->name('beauty-trial.landing');
Route::post('/social/beauty', [TrialLandingController::class, 'store'])->defaults('variant', 'beauty')->middleware('throttle:5,1')->name('beauty-trial.lead');

Route::get('/social/antiquariato-v2', [AntiquesLandingController::class, 'show'])->name('antiques.landing');
Route::post('/social/antiquariato-v2', [AntiquesLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('antiques.lead');

Route::get('/social/antiquariato', [TrialLandingController::class, 'show'])->defaults('variant', 'antiques')->name('antiques-trial.landing');
Route::post('/social/antiquariato', [TrialLandingController::class, 'store'])->defaults('variant', 'antiques')->middleware('throttle:5,1')->name('antiques-trial.lead');

Route::get('/social/pasticcerie-v2', [PastryLandingController::class, 'show'])->name('pastry.landing');
Route::post('/social/pasticcerie-v2', [PastryLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('pastry.lead');

Route::get('/social/pasticcerie', [TrialLandingController::class, 'show'])->defaults('variant', 'pastry')->name('pastry-trial.landing');
Route::post('/social/pasticcerie', [TrialLandingController::class, 'store'])->defaults('variant', 'pastry')->middleware('throttle:5,1')->name('pastry-trial.lead');

Route::get('/siti-v2', [WebsiteLandingController::class, 'show'])->name('sites.landing');
Route::post('/siti-v2', [WebsiteLandingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('sites.lead');

Route::get('/siti', [TrialLandingController::class, 'show'])->defaults('variant', 'sites')->name('sites-trial.landing');
Route::post('/siti', [TrialLandingController::class, 'store'])->defaults('variant', 'sites')->middleware('throttle:5,1')->name('sites-trial.lead');

Route::post('/tracking/consent', [MarketingConsentController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('tracking.consent');

Route::post('/tracking/form-start', [FormProgressTrackingController::class, 'started'])
    ->middleware('throttle:60,1')
    ->name('tracking.form-start');

Route::post('/tracking/form-step-2', [FormProgressTrackingController::class, 'stepTwo'])
    ->middleware('throttle:60,1')
    ->name('tracking.form-step-2');

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
