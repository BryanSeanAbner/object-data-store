<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ImageCropController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('articles.index');
});

// Halaman Tampilan Artikel (5 Varian Card)
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');

// Halaman Form / Kelola Artikel (CRUD)
Route::get('/form-articles', [ArticleController::class, 'form'])->name('articles.form');
Route::post('/form-articles', [ArticleController::class, 'store'])->name('articles.store');
Route::put('/form-articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
Route::delete('/form-articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');

// On-the-fly Image Resizer & Cropper (Thumbor Architecture)
Route::get('/crop/{crop}/{dimensions}/{path}', [ImageCropController::class, 'crop'])
    ->where('path', '.*')
    ->name('image.crop');

Route::get('/lupa-password', [ForgotPasswordController::class, 'show'])->name('password.request');

Route::post('/lupa-password', [ForgotPasswordController::class, 'sendLink'])
    ->middleware('throttle:5,1')
    ->name('password.email');

Route::get('lupa-password/{token}', [ForgotPasswordController::class, 'show'])->name('password.reset');

Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])
    ->middleware('throttle:5,1')
    ->name('password.update');
