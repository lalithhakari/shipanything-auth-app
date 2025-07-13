<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "Auth App - INSTANT UPDATES! ⚡️ Time: " . date('H:i:s') . " - Hot reload confirmed working! 🔥";
});
