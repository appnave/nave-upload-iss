<?php

namespace Bildvitta\IssUpload;

use Bildvitta\IssUpload\Http\Requests\UploadRequest;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Class IssUploadServiceProvider.
 */
class IssUploadServiceProvider extends PackageServiceProvider
{
    public function packageRegistered(): void
    {
        $uploadRequest = new UploadRequest(request()->all());
        $this->app->singleton(IssUploadContract::class, fn () => new IssUpload($uploadRequest->entity, $uploadRequest->filename, $uploadRequest->mime_type));
    }

    public function configurePackage(Package $package): void
    {
        $package->name('iss-upload')->hasConfigFile()->hasRoute('api');
    }
}
