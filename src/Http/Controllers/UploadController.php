<?php

namespace Bildvitta\IssUpload\Http\Controllers;

use Bildvitta\IssUpload\Http\Requests\UploadRequest;
use Bildvitta\IssUpload\IssUploadContract;
use Illuminate\Http\JsonResponse;

class UploadController extends Controller
{
    public function __construct(private IssUploadContract $issUpload)
    {
    }

    public function __invoke(UploadRequest $uploadRequest): JsonResponse
    {
        return response()->json($this->issUpload->getUploadSource());
    }
}
