<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

Route::get('login/reset', [Controllers\ResetPasswordController::class, 'reset'])->name('password.reset');
