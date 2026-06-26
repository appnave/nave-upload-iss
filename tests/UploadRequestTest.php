<?php

namespace Bildvitta\IssUpload\Tests;

use Bildvitta\IssUpload\Http\Requests\UploadRequest;
use Illuminate\Support\Facades\Validator;

class UploadRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new UploadRequest())->rules();
    }

    public function test_valid_request_passes(): void
    {
        $validator = Validator::make(
            ['filename' => 'photo.png', 'entity' => 'docs', 'mime_type' => 'image/png'],
            $this->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_filename_is_required(): void
    {
        $validator = Validator::make(
            ['entity' => 'docs', 'mime_type' => 'image/png'],
            $this->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('filename', $validator->errors()->toArray());
    }

    public function test_entity_is_required(): void
    {
        $validator = Validator::make(
            ['filename' => 'photo.png', 'mime_type' => 'image/png'],
            $this->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('entity', $validator->errors()->toArray());
    }

    public function test_invalid_mime_type_fails(): void
    {
        $validator = Validator::make(
            ['filename' => 'script.exe', 'entity' => 'docs', 'mime_type' => 'application/x-msdownload'],
            $this->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('mime_type', $validator->errors()->toArray());
    }

    public function test_mime_type_is_detected_from_filename_extension(): void
    {
        $request = new UploadRequest(['filename' => 'photo.png', 'entity' => 'docs']);
        $request->prepareForValidation();

        $this->assertSame('image/png', $request->get('mime_type'));
    }

    public function test_pdf_mime_type_is_allowed_by_default(): void
    {
        $validator = Validator::make(
            ['filename' => 'document.pdf', 'entity' => 'docs', 'mime_type' => 'application/pdf'],
            $this->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_jpeg_mime_type_is_allowed_by_default(): void
    {
        $validator = Validator::make(
            ['filename' => 'photo.jpg', 'entity' => 'photos', 'mime_type' => 'image/jpeg'],
            $this->rules()
        );

        $this->assertFalse($validator->fails());
    }
}
