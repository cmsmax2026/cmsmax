<?php

namespace App\Support\PackageManifest;

class PackageLinkManager
{
    public static function syncManagedLinks(array $manifest): array
    {
        $results = [];
        foreach (static::resolveLinks($manifest) as $link => $target) {
            $results[] = [
                'link' => $link,
                'target' => $target,
                'status' => static::syncLink($link, $target),
            ];
        }

        return $results;
    }

    public static function removeManagedLinks(array $manifest): array
    {
        $results = [];
        foreach (static::resolveLinks($manifest) as $link => $target) {
            $results[] = [
                'link' => $link,
                'target' => $target,
                'status' => static::removeLink($link),
            ];
        }

        return $results;
    }

    private static function resolveLinks(array $manifest): array
    {
        $links = $manifest['ui']['links'] ?? [];
        if (!is_array($links) || empty($links)) {
            $links = $manifest['links'] ?? [];
        }

        if ((!is_array($links) || empty($links))
            && ($manifest['package_type'] ?? '') === PackageManifest::PACKAGE_MODULE
            && !empty($manifest['identification'])) {
            $links = [
                public_path('views/modules/' . strtolower($manifest['identification'])) => module_path(
                    $manifest['identification'],
                    'Resources/views'
                ),
            ];
        }

        $resolved = [];
        foreach ((array) $links as $link => $target) {
            $link = trim((string) $link);
            $target = trim((string) $target);
            if ($link === '' || $target === '') {
                continue;
            }
            $resolved[$link] = $target;
        }

        return $resolved;
    }

    private static function syncLink(string $link, string $target): string
    {
        if (!file_exists($target) && !is_link($target)) {
            return 'target_missing';
        }

        $linkDir = dirname($link);
        if (!is_dir($linkDir)) {
            @mkdir($linkDir, 0777, true);
        }

        if (file_exists($link) || is_link($link)) {
            $linkReal = @realpath($link);
            $targetReal = @realpath($target);
            if ($linkReal && $targetReal && $linkReal === $targetReal) {
                return 'ok';
            }

            if (!static::removeLinkPath($link)) {
                return 'skip_existing';
            }
        }

        return @symlink($target, $link) ? 'created' : 'failed';
    }

    private static function removeLink(string $link): string
    {
        if (!file_exists($link) && !is_link($link)) {
            return 'absent';
        }

        return static::removeLinkPath($link) ? 'removed' : 'failed';
    }

    private static function removeLinkPath(string $path): bool
    {
        if (is_link($path) || is_file($path)) {
            return @unlink($path);
        }

        if (is_dir($path)) {
            return @rmdir($path);
        }

        return false;
    }
}
