<?php

namespace Bildvitta\IssUpload\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;

use function config;

/**
 * Class UploadRequest
 *
 * @property-read string entity
 * @property-read string filename
 * @property-read string mime_type
 */
class UploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $this->merge([
            'mime_type' => (new ExtensionMimeTypeDetector())->detectMimeTypeFromPath((string) $this->filename),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mime_type' => 'in:'.config('iss-upload.validation.mime_type'),
            'filename'  => 'required',
            'entity'    => 'required',
        ];
    }
}
