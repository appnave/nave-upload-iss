<?php

namespace Bildvitta\IssUpload\Commands;

use Illuminate\Console\Command;

/**
 * Class IssImageCommand.
 */
class IssImageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    public $signature = 'iss-upload';

    /**
     * The console command description.
     *
     * @var string|null
     */
    public $description = 'My command';

    public function handle(): void
    {
        $this->comment('All done');
    }
}
