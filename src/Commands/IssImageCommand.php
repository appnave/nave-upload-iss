<?php

namespace Bildvitta\IssUpload\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class IssImageCommand extends Command
{
    public $signature = 'iss-upload:check';

    public $description = 'Check ISS Upload storage configuration';

    public function handle(): int
    {
        $this->info('ISS Upload configuration');
        $this->line('');

        $disk = config('iss-upload.disk', 's3');
        $acl = config('iss-upload.acl', 'public-read');
        $expiry = config('iss-upload.presigned_expiry', '+10 minutes');
        $prefix = config('iss-upload.path_prefix', 'uploads');

        $this->table(['Setting', 'Value'], [
            ['Disk', $disk],
            ['ACL', $acl],
            ['Presigned URL expiry', $expiry],
            ['Path prefix', $prefix],
        ]);

        $this->line('');
        $this->line('Testing storage connection...');

        try {
            Storage::disk($disk)->exists('__iss_upload_check__');
            $this->info("Storage disk \"{$disk}\" is reachable.");
        } catch (Throwable $e) {
            $this->error("Could not connect to disk \"{$disk}\": {$e->getMessage()}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
