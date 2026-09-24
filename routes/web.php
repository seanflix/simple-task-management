<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'index'])->name('home');

Route::controller(ProjectController::class)
    ->prefix('projects')
    ->name('projects.')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::patch('/{project}', 'update')->name('update');
        Route::delete('/{project}', 'destroy')->name('destroy');
    });

Route::controller(TaskController::class)
    ->prefix('tasks')
    ->name('tasks.')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::patch('/reorder', 'reorder')->name('reorder');
        Route::patch('/{task}', 'update')->name('update');
        Route::delete('/{task}', 'destroy')->name('destroy');
    });
