<?php

namespace Bildvitta\IssUpload;

use Illuminate\Support\Facades\Facade;
use RuntimeException;

/**
 * @see \Bildvitta\IssUpload\IssUpload
 */
class IssUploadFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     *
     * @throws RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'iss-upload';
    }
}
