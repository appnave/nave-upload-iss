<?php

use Bildvitta\IssUpload\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

$configRoute = config('iss-upload.route');

Route::prefix($configRoute['prefix'])->middleware($configRoute['middleware'])->group(function () {
    Route::post('/upload-credentials')->name('upload_credentials')->uses(UploadController::class);
});
