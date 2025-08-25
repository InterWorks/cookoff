<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataExportController;

Route::middleware('api.auth')->group(function () {
    Route::get('/export/contests', [DataExportController::class, 'contests'])
        ->middleware('api.auth:export.contests');
    Route::get('/export/votes', [DataExportController::class, 'votes'])
        ->middleware('api.auth:export.votes');
    Route::get('/export/entries', [DataExportController::class, 'entries'])
        ->middleware('api.auth:export.entries');
    Route::get('/export/vote-ratings', [DataExportController::class, 'voteRatings'])
        ->middleware('api.auth:export.vote-ratings');
    Route::get('/export/contest/{contest}', [DataExportController::class, 'singleContest'])
        ->middleware('api.auth:export.single-contest');
});
