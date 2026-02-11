<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--retention=7 : Number of days to keep backups}';
    protected $description = 'Backup the database to S3 with retention policy';

    public function handle()
    {
        $this->info('Starting database backup...');

        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/' . $filename);
        $compressedPath = $path . '.gz';

        // 1. Dump Database
        $this->info('Dumping database...');
        $process = Process::fromShellCommandline(sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            $path
        ));

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $this->error('The backup process has been failed.');
            return 1;
        }

        // 2. Compress
        $this->info('Compressing backup...');
        $process = Process::fromShellCommandline("gzip $path");
        $process->mustRun();

        // 3. Upload to S3
        $this->info('Uploading to S3...');
        try {
            Storage::disk('s3')->put('backups/' . basename($compressedPath), file_get_contents($compressedPath));
            $this->info('Backup uploaded successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to upload to S3: ' . $e->getMessage());
            return 1;
        }

        // 4. Cleanup Local
        unlink($compressedPath);

        // 5. Retention Policy
        $this->cleanupOldBackups();

        return 0;
    }

    protected function cleanupOldBackups()
    {
        $retentionDays = $this->option('retention');
        $this->info("Cleaning up backups older than $retentionDays days...");

        $files = Storage::disk('s3')->files('backups');
        
        foreach ($files as $file) {
            $lastModified = Storage::disk('s3')->lastModified($file);
            $date = Carbon::createFromTimestamp($lastModified);

            if ($date->diffInDays(Carbon::now()) > $retentionDays) {
                Storage::disk('s3')->delete($file);
                $this->info("Deleted old backup: $file");
            }
        }
    }
}
