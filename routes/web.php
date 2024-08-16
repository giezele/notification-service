<?php

use App\Http\Controllers\TwilioSMSController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

