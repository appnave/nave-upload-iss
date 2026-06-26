<?php

namespace Bildvitta\IssUpload\Tests;

class PublishConfigCommandTest extends TestCase
{
    private string $targetPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->targetPath = config_path('iss-upload.php');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->targetPath)) {
            unlink($this->targetPath);
        }
        parent::tearDown();
    }

    public function test_publishes_config_when_it_does_not_exist(): void
    {
        $this->assertFileDoesNotExist($this->targetPath);

        $this->artisan('iss-upload:publish-config')->assertSuccessful();

        $this->assertFileExists($this->targetPath);
        $published = require $this->targetPath;
        $this->assertArrayHasKey('disk', $published);
        $this->assertArrayHasKey('acl', $published);
        $this->assertArrayHasKey('presigned_expiry', $published);
        $this->assertArrayHasKey('path_prefix', $published);
        $this->assertArrayHasKey('validation', $published);
        $this->assertArrayHasKey('route', $published);
    }

    public function test_reports_up_to_date_when_config_is_complete(): void
    {
        // Publish once to create the file
        $this->artisan('iss-upload:publish-config');

        // Run again — should report nothing to add
        $this->artisan('iss-upload:publish-config')
            ->assertSuccessful()
            ->expectsOutput('Config is already up to date. No new keys to add.');
    }

    public function test_adds_missing_keys_to_existing_config(): void
    {
        // Simulate an old config missing the new keys (disk, acl, presigned_expiry, path_prefix)
        file_put_contents($this->targetPath, <<<'PHP'
<?php

return [
    'validation' => [
        'mime_type' => env('ISS_IMAGE_VALID_MIME_TYPE', 'image/jpeg,image/gif,image/bmp,image/tiff,image/png,application/pdf'),
    ],

    'route' => [
        'prefix' => env('ISS_IMAGE_ROUTE_PREFIX', 'api'),

        'middleware' => env('ISS_IMAGE_ROUTE_MIDDLEWARE', 'hub.auth'),
    ],
];
PHP);

        $this->artisan('iss-upload:publish-config')->assertSuccessful();

        $updated = require $this->targetPath;

        $this->assertArrayHasKey('disk', $updated);
        $this->assertArrayHasKey('acl', $updated);
        $this->assertArrayHasKey('presigned_expiry', $updated);
        $this->assertArrayHasKey('path_prefix', $updated);

        // Original keys must still be present
        $this->assertArrayHasKey('validation', $updated);
        $this->assertArrayHasKey('route', $updated);
    }

    public function test_missing_keys_preserve_env_calls(): void
    {
        file_put_contents($this->targetPath, <<<'PHP'
<?php

return [
    'validation' => [
        'mime_type' => env('ISS_IMAGE_VALID_MIME_TYPE', 'image/jpeg'),
    ],

    'route' => [
        'prefix' => env('ISS_IMAGE_ROUTE_PREFIX', 'api'),
        'middleware' => env('ISS_IMAGE_ROUTE_MIDDLEWARE', 'hub.auth'),
    ],
];
PHP);

        $this->artisan('iss-upload:publish-config');

        $content = file_get_contents($this->targetPath);

        $this->assertStringContainsString("env('ISS_UPLOAD_DISK'", $content);
        $this->assertStringContainsString("env('ISS_UPLOAD_ACL'", $content);
        $this->assertStringContainsString("env('ISS_UPLOAD_PRESIGNED_EXPIRY'", $content);
        $this->assertStringContainsString("env('ISS_UPLOAD_PATH_PREFIX'", $content);
    }
}
