<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');

Route::middleware('auth')->group(function () {
    Route::get('/top', [TodoController::class, 'index'])->name('top');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::get('/todos/completed', [TodoController::class, 'completed'])->name('todos.completed');
    Route::get('/todos/{todoId}', [TodoController::class, 'show'])->whereNumber('todoId')->name('todos.show');
    Route::get('/todos/{todoId}/edit', [TodoController::class, 'edit'])->whereNumber('todoId')->name('todos.edit');
    Route::patch('/todos/{todoId}', [TodoController::class, 'update'])->whereNumber('todoId')->name('todos.update');
    Route::patch('/todos/{todoId}/complete', [TodoController::class, 'complete'])->whereNumber('todoId')->name('todos.complete');
    Route::delete('/todos/{todoId}', [TodoController::class, 'destroy'])->whereNumber('todoId')->name('todos.destroy');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
