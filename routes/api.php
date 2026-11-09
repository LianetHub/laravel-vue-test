<?php

use App\Http\Controllers\Api\AssignedShiftController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ShiftTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/shift-templates', [ShiftTemplateController::class, 'index']);

Route::get('/schedules', [ScheduleController::class, 'index']);
Route::post('/schedules', [ScheduleController::class, 'store']);
Route::get('/schedules/{schedule}', [ScheduleController::class, 'show']);
Route::put('/schedules/{schedule}', [ScheduleController::class, 'update']);
Route::post('/schedules/{schedule}/save', [ScheduleController::class, 'save']);
Route::post('/schedules/{schedule}/publish', [ScheduleController::class, 'publish']);

Route::get('/schedules/{schedule}/shifts', [AssignedShiftController::class, 'index']);
Route::post('/schedules/{schedule}/shifts', [AssignedShiftController::class, 'store']);
Route::put('/shifts/{shift}', [AssignedShiftController::class, 'update']);
Route::delete('/shifts/{shift}', [AssignedShiftController::class, 'destroy']);
