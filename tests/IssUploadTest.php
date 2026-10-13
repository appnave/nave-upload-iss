<?php

namespace Bildvitta\IssUpload\Tests;

use Bildvitta\IssUpload\IssUpload;
use Bildvitta\IssUpload\IssUploadContract;
use Illuminate\Support\Facades\Storage;
use Mockery;

class IssUploadTest extends TestCase
{
    public function test_implements_contract(): void
    {
        $this->assertInstanceOf(IssUploadContract::class, new IssUpload());
    }

    public function test_setters_return_contract_for_chaining(): void
    {
        $upload = new IssUpload();

        $this->assertInstanceOf(IssUploadContract::class, $upload->setEntity('docs'));
        $this->assertInstanceOf(IssUploadContract::class, $upload->setFilename('photo.png'));
        $this->assertInstanceOf(IssUploadContract::class, $upload->setMimeType('image/png'));
    }

    /**
     * Build a fake disk mock that follows the Flysystem v3 path:
     * method_exists($driver, 'getAdapter') returns false, so $adapter = $storage (the disk itself).
     * The disk must then have getClient() and getConfig() registered.
     */
    private function makeDiskMock(\Aws\S3\S3Client $s3Client): \Mockery\MockInterface
    {
        $disk = Mockery::mock();
        // getDriver() returns an anonymous mock with no real methods,
        // so method_exists($driver, 'getAdapter') === false → Flysystem v3 path.
        $disk->shouldReceive('getDriver')->andReturn(Mockery::mock());
        // With Flysystem v3, the disk itself acts as the adapter.
        $disk->shouldReceive('getClient')->andReturn($s3Client);
        // method_exists($adapter, 'getBucket') === false → falls through to getConfig().
        $disk->shouldReceive('getConfig')->andReturn(['bucket' => 'my-bucket']);

        return $disk;
    }

    private function makeS3ClientMock(string $presignedUri): \Aws\S3\S3Client
    {
        $presignedRequest = Mockery::mock();
        $presignedRequest->shouldReceive('getUri')->andReturn($presignedUri);

        $command = Mockery::mock(\Aws\CommandInterface::class);

        $s3Client = Mockery::mock(\Aws\S3\S3Client::class);
        $s3Client->shouldReceive('getCommand')->andReturn($command);
        $s3Client->shouldReceive('createPresignedRequest')->andReturn($presignedRequest);

        return $s3Client;
    }

    public function test_get_upload_source_returns_expected_keys(): void
    {
        $presignedUri = 'https://my-bucket.s3.amazonaws.com/uploads/docs/photo.png?X-Amz-Algorithm=AWS4-HMAC-SHA256';

        $s3Client = $this->makeS3ClientMock($presignedUri);
        Storage::shouldReceive('disk')->with('s3')->andReturn($this->makeDiskMock($s3Client));

        $result = (new IssUpload('docs', 'photo.png', 'image/png'))->getUploadSource();

        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('full_path', $result);
        $this->assertArrayHasKey('endpoint', $result);
    }

    public function test_key_uses_configured_path_prefix(): void
    {
        config(['iss-upload.path_prefix' => 'media']);

        $presignedUri = 'https://my-bucket.s3.amazonaws.com/media/docs/photo.png?sig=abc';

        $s3Client = $this->makeS3ClientMock($presignedUri);
        Storage::shouldReceive('disk')->andReturn($this->makeDiskMock($s3Client));

        $result = (new IssUpload('docs', 'photo.png', 'image/png'))->getUploadSource();

        $this->assertSame('media/docs/photo.png', $result['path']);
    }

    public function test_full_path_is_built_from_presigned_url_host(): void
    {
        $presignedUri = 'https://my-bucket.s3.amazonaws.com/uploads/docs/photo.png?sig=abc';

        $s3Client = $this->makeS3ClientMock($presignedUri);
        Storage::shouldReceive('disk')->andReturn($this->makeDiskMock($s3Client));

        $result = (new IssUpload('docs', 'photo.png', 'image/png'))->getUploadSource();

        $this->assertSame($presignedUri, $result['endpoint']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
