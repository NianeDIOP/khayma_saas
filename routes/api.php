<?php

use App\Http\Controllers\Api\WorkflowProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/workflow/{phase}', [WorkflowProgressController::class, 'show']);
Route::post('/workflow/{phase}', [WorkflowProgressController::class, 'update']);
