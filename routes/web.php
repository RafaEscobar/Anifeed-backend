<?php

use Illuminate\Support\Facades\Route;
use App\Jobs\SyncRssFeed;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-job', function () {
    SyncRssFeed::dispatch();
    return 'Job enviado 🚀';
});