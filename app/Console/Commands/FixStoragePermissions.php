<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class FixStoragePermissions extends Command
{
    protected $signature = 'storage:fix-permissions
        {--dir-mode=775 : Permission untuk folder}
        {--file-mode=664 : Permission untuk file}
        {--owner= : Owner (contoh: www)}
        {--group= : Group (contoh: www)}
        {--include-public-storage : Ikut set permission public/storage (jika ada folder/symlink)}
        {--dry-run : Tampilkan aksi tanpa eksekusi}';

    protected $description = 'Perbaiki permission (dan optional ownership) untuk storage dan bootstrap/cache';

    public function handle(): int
    {
        $dirMode = $this->parseMode((string) $this->option('dir-mode'));
        $fileMode = $this->parseMode((string) $this->option('file-mode'));
        $owner = $this->option('owner');
        $group = $this->option('group');
        $dryRun = (bool) $this->option('dry-run');

        if ($dirMode === null || $fileMode === null) {
            $this->error('Mode permission tidak valid. Contoh: --dir-mode=775 --file-mode=664');
            return self::FAILURE;
        }

        $targets = [
            base_path('storage'),
            base_path('bootstrap/cache'),
        ];

        if ($this->option('include-public-storage')) {
            $targets[] = public_path('storage');
        }

        $targets = array_values(array_filter($targets, fn ($p) => is_string($p) && $p !== ''));

        foreach ($targets as $target) {
            if (!file_exists($target)) {
                $this->warn("Path tidak ditemukan: {$target}");
                continue;
            }

            $this->line("Target: {$target}");

            if (is_dir($target)) {
                $this->applyToDirectory($target, $dirMode, $fileMode, $owner, $group, $dryRun);
                $this->applyPathPermissions($target, $dirMode, $owner, $group, $dryRun);
                continue;
            }

            $this->applyPathPermissions($target, $fileMode, $owner, $group, $dryRun);
        }

        $this->info('Selesai.');
        return self::SUCCESS;
    }

    private function applyToDirectory(string $root, int $dirMode, int $fileMode, $owner, $group, bool $dryRun): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $path = $item->getPathname();

            if (is_link($path)) {
                continue;
            }

            if ($item->isDir()) {
                $this->applyPathPermissions($path, $dirMode, $owner, $group, $dryRun);
            } elseif ($item->isFile()) {
                $this->applyPathPermissions($path, $fileMode, $owner, $group, $dryRun);
            }
        }
    }

    private function applyPathPermissions(string $path, int $mode, $owner, $group, bool $dryRun): void
    {
        if ($dryRun) {
            $this->line("DRY-RUN chmod " . decoct($mode) . " {$path}");
        } else {
            @chmod($path, $mode);
        }

        if (is_string($owner) && $owner !== '') {
            if ($dryRun) {
                $this->line("DRY-RUN chown {$owner} {$path}");
            } else {
                @chown($path, $owner);
            }
        }

        if (is_string($group) && $group !== '') {
            if ($dryRun) {
                $this->line("DRY-RUN chgrp {$group} {$path}");
            } else {
                @chgrp($path, $group);
            }
        }
    }

    private function parseMode(string $mode): ?int
    {
        $mode = trim($mode);
        if ($mode === '') {
            return null;
        }

        if (!preg_match('/^[0-7]{3,4}$/', $mode)) {
            return null;
        }

        return octdec($mode);
    }
}

