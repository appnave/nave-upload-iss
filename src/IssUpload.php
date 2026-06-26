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

        $cmd = $client->getCommand('PutObject', [
            'ACL'         => config('iss-upload.acl', 'public-read'),
            'Bucket'      => $bucket,
            'Key'         => $key,
            'ContentType' => $this->mimeType,
        ]);

        $response = (string) $client->createPresignedRequest($cmd, config('iss-upload.presigned_expiry', '+10 minutes'))->getUri();

        $parsed = parse_url($response);

        if (! isset($parsed['scheme'], $parsed['host'])) {
            throw new \RuntimeException('Invalid presigned URL returned by S3 client.');
        }

        $full_path = sprintf('%s://%s/%s', $parsed['scheme'], $parsed['host'], $key);

        return [
            'path'      => $key,
            'full_path' => $full_path,
            'endpoint'  => $response,
        ];
    }
}
