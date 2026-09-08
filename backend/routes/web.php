<?php

use Illuminate\Support\Facades\Route;

// This backend is an API only — the actual UI is the React app in /frontend.
// This root route just confirms the API is up if someone visits the bare domain.
Route::get('/', function () {
    return response()->json(['message' => 'Survey App API — see /api for endpoints.']);
});
