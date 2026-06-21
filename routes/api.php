<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookLookupController;
use App\Http\Controllers\Api\DigitalEpisodeController;
use App\Http\Controllers\Api\DigitalPlatformController;
use App\Http\Controllers\Api\DigitalSeriesController;
use App\Http\Controllers\Api\PhysicalVolumeController;
use App\Http\Controllers\Api\UserDigitalEpisodeReadController;
use App\Http\Controllers\Api\UserDigitalTrackingController;
use App\Http\Controllers\Api\UserPhysicalCollectionController;
use App\Http\Controllers\Api\UserWorkTrackingController;
use App\Http\Controllers\Api\WorkController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('works', WorkController::class);
    Route::get('/works/{work}/physical-volumes', [PhysicalVolumeController::class, 'index']);
    Route::post('/works/{work}/physical-volumes', [PhysicalVolumeController::class, 'store']);
    Route::get('/physical-volumes/{physicalVolume}', [PhysicalVolumeController::class, 'show']);
    Route::put('/physical-volumes/{physicalVolume}', [PhysicalVolumeController::class, 'update']);
    Route::delete('/physical-volumes/{physicalVolume}', [PhysicalVolumeController::class, 'destroy']);

    Route::get('/book-lookup/isbn/{isbn}', [BookLookupController::class, 'isbn']);
    Route::get('/book-lookup/search', [BookLookupController::class, 'search']);
    Route::post('/book-lookup/import', [BookLookupController::class, 'import']);

    Route::get('/my/physical-collection', [UserPhysicalCollectionController::class, 'index']);
    Route::post('/my/physical-collection', [UserPhysicalCollectionController::class, 'store']);
    Route::put('/my/physical-collection/{item}', [UserPhysicalCollectionController::class, 'update']);
    Route::delete('/my/physical-collection/{item}', [UserPhysicalCollectionController::class, 'destroy']);

    Route::get('/my/work-tracking', [UserWorkTrackingController::class, 'index']);
    Route::post('/my/work-tracking', [UserWorkTrackingController::class, 'store']);
    Route::put('/my/work-tracking/{tracking}', [UserWorkTrackingController::class, 'update']);
    Route::delete('/my/work-tracking/{tracking}', [UserWorkTrackingController::class, 'destroy']);

    Route::apiResource('digital-platforms', DigitalPlatformController::class);
    Route::apiResource('digital-series', DigitalSeriesController::class);
    Route::get('/digital-series/{digitalSeries}/episodes', [DigitalEpisodeController::class, 'index']);
    Route::post('/digital-series/{digitalSeries}/episodes', [DigitalEpisodeController::class, 'store']);
    Route::put('/digital-episodes/{digitalEpisode}', [DigitalEpisodeController::class, 'update']);
    Route::delete('/digital-episodes/{digitalEpisode}', [DigitalEpisodeController::class, 'destroy']);

    Route::get('/my/digital-tracking', [UserDigitalTrackingController::class, 'index']);
    Route::post('/my/digital-tracking', [UserDigitalTrackingController::class, 'store']);
    Route::put('/my/digital-tracking/{tracking}', [UserDigitalTrackingController::class, 'update']);
    Route::delete('/my/digital-tracking/{tracking}', [UserDigitalTrackingController::class, 'destroy']);

    Route::post('/my/digital-episode-reads', [UserDigitalEpisodeReadController::class, 'store']);
    Route::put('/my/digital-episode-reads/{read}', [UserDigitalEpisodeReadController::class, 'update']);
    Route::delete('/my/digital-episode-reads/{read}', [UserDigitalEpisodeReadController::class, 'destroy']);

    Route::get('/my/alerts', [AlertController::class, 'index']);
    Route::post('/my/alerts', [AlertController::class, 'store']);
    Route::put('/my/alerts/{alert}/read', [AlertController::class, 'markRead']);
    Route::put('/my/alerts/{alert}/dismiss', [AlertController::class, 'dismiss']);
    Route::delete('/my/alerts/{alert}', [AlertController::class, 'destroy']);
});
