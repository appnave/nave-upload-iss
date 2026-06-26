<?php

namespace Bildvitta\IssUpload;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bildvitta\IssUpload\IssUpload
 */
class IssUploadFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IssUploadContract::class;
    }
}
