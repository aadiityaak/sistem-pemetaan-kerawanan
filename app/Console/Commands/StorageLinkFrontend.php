<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StorageLinkFrontend extends Command
{
    protected $signature = 'storage:link-frontend {--public-dir= : Folder web root di luar laravel-app (contoh: public atau public_html)} {--force : Hapus link/folder existing jika ada} {--relative : Buat symlink relative (recommended untuk aaPanel)}';

    protected $description = 'Buat symlink /storage pada web root (public) yang mengarah ke storage/app/public (untuk setup aaPanel)';

    public function handle(): int
    {
        $target = storage_path('app/public');
        if (!is_dir($target)) {
            @mkdir($target, 0775, true);
        }

        $publicDir = $this->resolvePublicDir();
        if (!$publicDir) {
            $this->error('Folder web root tidak ditemukan. Gunakan --public-dir=public atau --public-dir=public_html.');
            return self::FAILURE;
        }

        $link = rtrim($publicDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'storage';

        $targetForLink = $this->option('relative') ? $this->relativeTarget($publicDir, $target) : $target;

        if ($this->isCorrectLink($link, $target)) {
            $this->info('Storage link sudah benar: ' . $link);
            return self::SUCCESS;
        }

        if (file_exists($link) || is_link($link)) {
            if (!$this->option('force')) {
                $this->error("Path sudah ada: {$link}. Jalankan dengan --force untuk mengganti.");
                return self::FAILURE;
            }
            $this->removePath($link);
        }

        if (!is_dir($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }

        if (@symlink($targetForLink, $link) !== true) {
            $this->error('Gagal membuat symlink. Coba jalankan dengan permission yang cukup atau gunakan fallback copy manual.');
            $this->line('Target: ' . $targetForLink);
            $this->line('Link  : ' . $link);
            return self::FAILURE;
        }

        $this->info('Berhasil membuat storage link: ' . $link . ' -> ' . $targetForLink);
        return self::SUCCESS;
    }

    private function resolvePublicDir(): ?string
    {
        $opt = $this->option('public-dir');
        if (is_string($opt) && trim($opt) !== '') {
            $candidate = base_path('..' . DIRECTORY_SEPARATOR . trim($opt, DIRECTORY_SEPARATOR));
            return $candidate;
        }

        $candidates = [
            base_path('..' . DIRECTORY_SEPARATOR . 'public'),
            base_path('..' . DIRECTORY_SEPARATOR . 'public_html'),
            public_path(),
        ];

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function isCorrectLink(string $link, string $target): bool
    {
        if (!is_link($link)) {
            return false;
        }

        $linkTarget = readlink($link);
        if (!is_string($linkTarget) || $linkTarget === '') {
            return false;
        }

        $resolvedTarget = realpath($target);
        if ($resolvedTarget === false) {
            return false;
        }

        $resolvedLinkTarget = $linkTarget;
        if (!preg_match('~^([A-Za-z]:\\\\|/)~', $linkTarget)) {
            $resolvedLinkTarget = realpath(dirname($link) . DIRECTORY_SEPARATOR . $linkTarget) ?: $linkTarget;
        }

        return $resolvedLinkTarget === $resolvedTarget;
    }

    private function removePath(string $path): void
    {
        if (is_link($path) || is_file($path)) {
            @unlink($path);
            return;
        }

        if (is_dir($path)) {
            $items = @scandir($path);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }
                    $this->removePath($path . DIRECTORY_SEPARATOR . $item);
                }
            }
            @rmdir($path);
        }
    }

    private function relativeTarget(string $publicDir, string $target): string
    {
        $from = rtrim(realpath($publicDir) ?: $publicDir, DIRECTORY_SEPARATOR);
        $to = rtrim(realpath($target) ?: $target, DIRECTORY_SEPARATOR);

        $fromParts = preg_split('~[\\\\/]~', $from) ?: [];
        $toParts = preg_split('~[\\\\/]~', $to) ?: [];

        $length = min(count($fromParts), count($toParts));
        $common = 0;
        for ($i = 0; $i < $length; $i++) {
            if ($fromParts[$i] !== $toParts[$i]) {
                break;
            }
            $common++;
        }

        $up = array_fill(0, count($fromParts) - $common, '..');
        $down = array_slice($toParts, $common);

        $rel = array_merge($up, $down);
        return implode(DIRECTORY_SEPARATOR, $rel);
    }
}

