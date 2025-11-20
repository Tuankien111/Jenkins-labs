<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Thêm route này để vào trang test
Route::view('/test-app', 'test-interface');