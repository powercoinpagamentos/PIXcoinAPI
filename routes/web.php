<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/frontend/{any?}', function () {
    return redirect('http://127.0.0.1:3000/' . request()->path());
})->where('any', '.*');
