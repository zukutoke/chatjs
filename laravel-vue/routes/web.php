<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All routes are handled by the Vue.js SPA. The API routes are in api.php
|
*/

// SPA catch-all route - must be last
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
