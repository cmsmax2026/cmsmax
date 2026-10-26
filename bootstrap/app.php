<?php

$mxzBasePath = $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);
$mxzLog = static function (array $data) use ($mxzBasePath): void {
    $frameworkDir = rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework';
    if (!is_dir($frameworkDir)) {
        @mkdir($frameworkDir, 0755, true);
    }

    $logFile = $frameworkDir . DIRECTORY_SEPARATOR . 'mxz-legacy-root-normalize.log';
    $payload = array_merge([
        'ts' => date('c'),
        'base' => $mxzBasePath,
        'sapi' => PHP_SAPI,
        'uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
    ], $data);
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        return;
    }
    @file_put_contents($logFile, $json . PHP_EOL, FILE_APPEND);
};

$mxzFindActualRootEntry = static function (string $name) use ($mxzBasePath): string {
    $baseDir = rtrim($mxzBasePath, '\\/');
    foreach (scandir($baseDir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        if (strcasecmp($entry, $name) === 0) {
            return (string) $entry;
        }
    }

    return '';
};

$mxzNormalizeLegacyRoot = static function (string $legacyName, string $targetName) use ($mxzBasePath, $mxzLog, $mxzFindActualRootEntry): void {
    $legacyRoot = rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . $legacyName;
    if (!is_dir($legacyRoot)) {
        return;
    }

    $targetRoot = rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . $targetName;
    $legacyReal = realpath($legacyRoot);
    $targetReal = is_dir($targetRoot) ? realpath($targetRoot) : false;
    if ($legacyReal && $targetReal && strcasecmp($legacyReal, $targetReal) === 0) {
        $frameworkDir = rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework';
        $marker = $frameworkDir . DIRECTORY_SEPARATOR . 'mxz-legacy-root-normalized-' . $targetName;
        $actualEntry = $mxzFindActualRootEntry($targetName);
        $actualRoot = $actualEntry !== '' ? (rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . $actualEntry) : $legacyRoot;
        $mxzLog([
            'legacy' => $legacyName,
            'target' => $targetName,
            'mode' => 'same_realpath_noop',
            'actual_entry' => $actualEntry,
            'legacy_is_link' => is_link($legacyRoot),
            'target_is_link' => is_link($targetRoot),
            'legacy_inode' => @fileinode($legacyRoot) ?: 0,
            'target_inode' => @fileinode($targetRoot) ?: 0,
        ]);

        if (!is_dir($frameworkDir)) {
            @mkdir($frameworkDir, 0755, true);
        }

        if ($actualEntry === $targetName) {
            @file_put_contents($marker, date('c'));
            $mxzLog([
                'legacy' => $legacyName,
                'target' => $targetName,
                'mode' => 'same_realpath_already_normalized',
                'actual_entry' => $actualEntry,
            ]);
            return;
        }

        if (is_file($marker)) {
            @unlink($marker);
        }

        if ($actualEntry !== '' && strcasecmp($actualEntry, $targetName) === 0) {
            $tempRoot = rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . '__mxz_casefix_' . $targetName . '_' . uniqid();
            if (@rename($actualRoot, $tempRoot)) {
                if (@rename($tempRoot, $targetRoot)) {
                    @file_put_contents($marker, date('c'));
                    $mxzLog([
                        'legacy' => $legacyName,
                        'target' => $targetName,
                        'mode' => 'normalized_by_temp_rename',
                        'actual_entry' => $actualEntry,
                        'temp_root' => $tempRoot,
                    ]);
                    return;
                }

                @rename($tempRoot, $actualRoot);
                $mxzLog([
                    'legacy' => $legacyName,
                    'target' => $targetName,
                    'mode' => 'temp_rename_to_target_failed',
                    'actual_entry' => $actualEntry,
                    'temp_root' => $tempRoot,
                    'error' => (string) ((error_get_last()['message'] ?? '') ?: ''),
                ]);
            } else {
                $mxzLog([
                    'legacy' => $legacyName,
                    'target' => $targetName,
                    'mode' => 'temp_rename_source_failed',
                    'actual_entry' => $actualEntry,
                    'temp_root' => $tempRoot,
                    'error' => (string) ((error_get_last()['message'] ?? '') ?: ''),
                ]);
            }
        }

        $backupDir = $frameworkDir . DIRECTORY_SEPARATOR . 'mxz-legacy-root-backup';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }

        $copied = 0;
        $copyFailed = 0;
        $copyTreeOverwrite = static function (string $source, string $target) use (&$copyTreeOverwrite, &$copied, &$copyFailed): void {
            if (!is_dir($source)) {
                return;
            }
            if (!is_dir($target)) {
                @mkdir($target, 0777, true);
            }
            foreach (scandir($source) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $sourcePath = $source . DIRECTORY_SEPARATOR . $entry;
                $targetPath = $target . DIRECTORY_SEPARATOR . $entry;
                if (is_dir($sourcePath)) {
                    $copyTreeOverwrite($sourcePath, $targetPath);
                    continue;
                }
                @mkdir(dirname($targetPath), 0777, true);
                if (@copy($sourcePath, $targetPath)) {
                    $copied++;
                } else {
                    $copyFailed++;
                }
            }
        };

        $removeTree = static function (string $path) use (&$removeTree): void {
            if (!is_dir($path)) {
                return;
            }
            foreach (scandir($path) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $child = $path . DIRECTORY_SEPARATOR . $entry;
                if (is_dir($child) && !is_link($child)) {
                    $removeTree($child);
                    continue;
                }
                @chmod($child, 0777);
                @unlink($child);
            }
            @rmdir($path);
        };

        $copyTreeOverwrite($targetRoot, $legacyRoot);
        $mxzLog([
            'legacy' => $legacyName,
            'target' => $targetName,
            'mode' => 'overwrite_legacy_by_target_same_realpath',
            'copied' => $copied,
            'copy_failed' => $copyFailed,
        ]);

        $backupTargetPath = $backupDir . DIRECTORY_SEPARATOR . $targetName . '_bak_' . date('YmdHis');
        if (!@rename($targetRoot, $backupTargetPath)) {
            $mxzLog([
                'legacy' => $legacyName,
                'target' => $targetName,
                'mode' => 'backup_target_failed_same_realpath',
                'backup' => $backupTargetPath,
                'error' => (string) ((error_get_last()['message'] ?? '') ?: ''),
            ]);
            return;
        }

        if (@rename($legacyRoot, $targetRoot)) {
            $removeTree($backupTargetPath);
            @file_put_contents($marker, date('c'));
            $mxzLog([
                'legacy' => $legacyName,
                'target' => $targetName,
                'mode' => 'normalized_by_copy_delete_rename',
                'backup_deleted' => 1,
            ]);
            return;
        }

        if (!is_dir($targetRoot)) {
            @rename($backupTargetPath, $targetRoot);
        }
        $mxzLog([
            'legacy' => $legacyName,
            'target' => $targetName,
            'mode' => 'rename_failed_same_realpath',
            'error' => (string) ((error_get_last()['message'] ?? '') ?: ''),
            'backup' => $backupTargetPath,
        ]);
        return;
    }

    $frameworkDir = rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework';
    $backupDir = $frameworkDir . DIRECTORY_SEPARATOR . 'mxz-legacy-root-backup';
    if (!is_dir($backupDir)) {
        @mkdir($backupDir, 0755, true);
    }

    $copied = 0;
    $copyFailed = 0;
    $copyTreeOverwrite = static function (string $source, string $target) use (&$copyTreeOverwrite, &$copied, &$copyFailed): void {
        if (!is_dir($source)) {
            return;
        }
        if (!is_dir($target)) {
            @mkdir($target, 0777, true);
        }
        foreach (scandir($source) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $sourcePath = $source . DIRECTORY_SEPARATOR . $entry;
            $targetPath = $target . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($sourcePath)) {
                $copyTreeOverwrite($sourcePath, $targetPath);
                continue;
            }
            @mkdir(dirname($targetPath), 0777, true);
            if (@copy($sourcePath, $targetPath)) {
                $copied++;
            } else {
                $copyFailed++;
            }
        }
    };

    $backupTargetPath = '';
    if (is_dir($targetRoot)) {
        $copyTreeOverwrite($targetRoot, $legacyRoot);
        $mxzLog([
            'legacy' => $legacyName,
            'target' => $targetName,
            'mode' => 'overwrite_legacy_by_target',
            'copied' => $copied,
            'copy_failed' => $copyFailed,
        ]);

        $backupTargetPath = $backupDir . DIRECTORY_SEPARATOR . $targetName . '_bak_' . date('YmdHis');
        if (@rename($targetRoot, $backupTargetPath)) {
            $mxzLog([
                'legacy' => $legacyName,
                'target' => $targetName,
                'mode' => 'backup_target_dir',
                'backup' => $backupTargetPath,
            ]);
        } else {
            $backupTargetPath = '';
            $mxzLog([
                'legacy' => $legacyName,
                'target' => $targetName,
                'mode' => 'backup_target_failed',
                'error' => (string) ((error_get_last()['message'] ?? '') ?: ''),
            ]);
            return;
        }
    }

    if (@rename($legacyRoot, $targetRoot)) {
        $mxzLog([
            'legacy' => $legacyName,
            'target' => $targetName,
            'mode' => 'rename_legacy_to_target',
            'backup_target' => $backupTargetPath,
        ]);
        return;
    }

    if ($backupTargetPath !== '' && is_dir($backupTargetPath) && !is_dir($targetRoot)) {
        @rename($backupTargetPath, $targetRoot);
    }
    $mxzLog([
        'legacy' => $legacyName,
        'target' => $targetName,
        'mode' => 'rename_failed',
        'error' => (string) ((error_get_last()['message'] ?? '') ?: ''),
        'backup_target' => $backupTargetPath,
    ]);

};

$mxzLog([
    'mode' => 'boot',
    'has_modules_legacy' => is_dir(rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'Modules'),
    'has_plugins_legacy' => is_dir(rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'Plugins'),
    'has_modules' => is_dir(rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'modules'),
    'has_plugins' => is_dir(rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'plugins'),
]);

$mxzNormalizeLegacyRoot('Modules', 'modules');
$mxzNormalizeLegacyRoot('Plugins', 'plugins');

$mxzLog([
    'mode' => 'boot_done',
    'has_modules_legacy' => is_dir(rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'Modules'),
    'has_plugins_legacy' => is_dir(rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'Plugins'),
    'has_modules' => is_dir(rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'modules'),
    'has_plugins' => is_dir(rtrim($mxzBasePath, '\\/') . DIRECTORY_SEPARATOR . 'plugins'),
]);

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $mxzBasePath
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
