<?php

namespace Bildvitta\IssUpload;

/**
 * Interface IssUploadContract.
 */
interface IssUploadContract
{
    public function getUploadSource(): array;

    public function setEntity(?string $entity): IssUploadContract;

    public function setFilename(?string $filename): IssUploadContract;

    public function setMimeType(?string $mimeType): IssUploadContract;
}
