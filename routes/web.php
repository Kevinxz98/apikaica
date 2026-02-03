<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chatbots/avatar/chatbots/{filename}', function ($filename) {
    $path = storage_path('app/public/chatbots/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $origin = request()->headers->get('Origin');

    $allowedOrigins = [
        'http://localhost:4200',
        'https://panel.kaica.co',
    ];

    if (in_array($origin, $allowedOrigins)) {
        return response()->file($path, [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => 'GET',
        ]);
    }
});
