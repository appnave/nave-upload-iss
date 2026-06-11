<?php

namespace Bildvitta\IssUpload;

/**
 * Trait IssUploadTrait.
 */
trait IssUploadTrait
{
    protected ?string $entity;

    protected ?string $filename;

    protected ?string $mimeType;

    public function setEntity(?string $entity): IssUploadContract
    {
        $this->entity = $entity;

        return $this;
    }

    public function setFilename(?string $filename): IssUploadContract
    {
        $this->filename = $filename;

        return $this;
    }

    public function setMimeType(?string $mimeType): IssUploadContract
    {
        $this->mimeType = $mimeType;

        return $this;
    }
}
