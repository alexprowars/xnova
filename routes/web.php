<?php

use Illuminate\Support\Facades\Route;

Route::get('/')->name('login');
Route::get('login/reset')->name('password.reset');
Route::get('galaxy')->name('galaxy');
