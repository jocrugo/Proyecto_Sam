<?php

use App\Http\Controllers\InterviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InterviewController::class, 'index'])->name('home');

Route::get('/entrevistas/crear', [InterviewController::class, 'create'])->name('interviews.create');
Route::post('/entrevistas', [InterviewController::class, 'store'])->name('interviews.store');
Route::get('/entrevistas/{interview}', [InterviewController::class, 'show'])->name('interviews.show');
Route::get('/entrevistas/{interview}/pdf', [InterviewController::class, 'exportPdf'])->name('interviews.export.pdf');
Route::post('/entrevistas/{interview}/mensajes', [InterviewController::class, 'storeMessages'])->name('interviews.messages.store');
Route::put('/entrevistas/{interview}/mensajes', [InterviewController::class, 'updateMessages'])->name('interviews.messages.update');
Route::get('/entrevistas/{interview}/editar', [InterviewController::class, 'edit'])->name('interviews.edit');
Route::put('/entrevistas/{interview}', [InterviewController::class, 'update'])->name('interviews.update');
Route::delete('/entrevistas/{interview}', [InterviewController::class, 'destroy'])->name('interviews.destroy');
