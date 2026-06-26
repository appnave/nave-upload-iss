<?php

namespace Bildvitta\IssUpload\Commands;

use Illuminate\Console\Command;

class PublishConfigCommand extends Command
{
    public $signature = 'iss-upload:publish-config';

    public $description = 'Publish the ISS Upload config, adding only missing keys when the file already exists';

    public function handle(): int
    {
        $sourcePath = __DIR__ . '/../../config/iss-upload.php';
        $targetPath = config_path('iss-upload.php');

        if (! file_exists($targetPath)) {
            copy($sourcePath, $targetPath);
            $this->info("Config published to {$targetPath}.");

            return self::SUCCESS;
        }

        $sourceKeys = array_keys(require $sourcePath);
        $projectKeys = array_keys(require $targetPath);
        $missingKeys = array_diff($sourceKeys, $projectKeys);

        if (empty($missingKeys)) {
            $this->info('Config is already up to date. No new keys to add.');

            return self::SUCCESS;
        }

        $blocks = $this->extractKeyBlocks($sourcePath);

        $newCode = '';
        foreach ($missingKeys as $key) {
            if (isset($blocks[$key])) {
                $newCode .= "\n" . $blocks[$key];
            }
        }

        $content = file_get_contents($targetPath);
        // Insert new keys before the final closing ];
        $content = preg_replace('/\];\s*$/', $newCode . "\n];", $content);
        file_put_contents($targetPath, $content);

        $this->info('Added ' . count($missingKeys) . ' new key(s): ' . implode(', ', $missingKeys));

        return self::SUCCESS;
    }

    /**
     * Parse the source config file and extract each top-level key's raw PHP code block.
     * Preserves env() calls and formatting from the original file.
     *
     * @return array<string, string>
     */
    private function extractKeyBlocks(string $sourcePath): array
    {
        $lines = file($sourcePath, FILE_IGNORE_NEW_LINES);
        $blocks = [];
        $currentKey = null;
        $currentLines = [];
        $depth = 0;

        foreach ($lines as $line) {
            if (preg_match('/^return \[/', $line) || trim($line) === '];') {
                continue;
            }

            if ($depth === 0 && preg_match("/^    '(\w+)'\s*=>/", $line, $matches)) {
                if ($currentKey !== null) {
                    $blocks[$currentKey] = implode("\n", $currentLines);
                }
                $currentKey = $matches[1];
                $currentLines = [$line];
                $depth += substr_count($line, '[') - substr_count($line, ']');
            } elseif ($currentKey !== null) {
                $currentLines[] = $line;
                $depth += substr_count($line, '[') - substr_count($line, ']');
            }
        }

        if ($currentKey !== null) {
            $blocks[$currentKey] = implode("\n", $currentLines);
        }

        return $blocks;
    }
}
