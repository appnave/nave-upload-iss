<?php

namespace Bildvitta\IssUpload;

use Illuminate\Support\Facades\Storage;

/**
 * Class IssUpload.
 */
class IssUpload implements IssUploadContract
{
    use IssUploadTrait;

    /**
     * IssUpload constructor.
     */
    public function __construct(?string $entity = null, ?string $filename = null, ?string $mimeType = null)
    {
        $this->entity = $entity;
        $this->filename = $filename;
        $this->mimeType = $mimeType;
    }

    public function getUploadSource(): array
    {
        $storage = Storage::disk(config('iss-upload.disk', 's3'));
        $adapter = method_exists($storage->getDriver(), 'getAdapter') ? $storage->getAdapter() : $storage;
        $client = $adapter->getClient();
        $bucket = method_exists($adapter, 'getBucket') ? $adapter->getBucket() : $adapter->getConfig()['bucket'];

        $key = sprintf('%s/%s/%s', config('iss-upload.path_prefix', 'uploads'), $this->entity, $this->filename);

        $cmdOptions = [
            'Bucket'      => $bucket,
            'Key'         => $key,
            'ContentType' => $this->mimeType,
        ];

        if (!empty(config('iss-upload.acl'))) {
            $cmdOptions['ACL'] = config('iss-upload.acl');
        }

        $cmd = $client->getCommand('PutObject', $cmdOptions);

        $endpoint = (string) $client->createPresignedRequest($cmd, config('iss-upload.presigned_expiry', '+10 minutes'))->getUri();

        $getCmdOptions = [
            'Bucket' => $bucket,
            'Key'    => $key,
        ];

        $getCmd = $client->getCommand('GetObject', $getCmdOptions);

        $full_path = (string) $client->createPresignedRequest($getCmd, '+7 days')->getUri();

        return [
            'path'      => $key,
            'full_path' => $full_path,
            'endpoint'  => $endpoint,
        ];
    }
}
