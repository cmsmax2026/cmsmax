<?php

namespace App\Support\Update;

use App\Support\PackageManifest\CompatibilityChecker;
use App\Support\PackageManifest\PackageManifest;

class UpdateMetaService
{
    public function checkApp(string $cloudHost, array $all): array
    {
        $return = [];
        $res = curl_request($cloudHost . '/api/cloud/checkapp?' . http_build_query($all));
        $res = json_decode($res, true);
        if (($res['status'] ?? 0) == 200 && !empty($res['data']['version'])) {
            if (version_compare($res['data']['version'], $all['version']) > 0) {
                $return = UpdateResponseFactory::success('有新版本');
                cache()->put($this->getAppUpdateCacheKey($all), $res['data'], 60 * 60 * 12);
            }
        }

        $this->syncUpdateLimit($all, $res['data'] ?? []);

        return $return;
    }

    public function checkCms(string $cloudHost, string $currentVersion): array
    {
        $return = [];
        $cached = cache()->get('cms_update_version');
        if (is_array($cached) && ($cached['version'] ?? '') === $currentVersion) {
            return $return;
        }

        $data = [
            'bycmsupdate' => 1,
            'version' => $currentVersion,
        ];
        $res = curl_request($cloudHost . '/api/cloud/getversion?' . http_build_query($data));
        $res = json_decode($res, true);
        if (($res['code'] ?? 0) == 200 && !empty($res['data']['version'])) {
            if (version_compare($res['data']['version'], $currentVersion) > 0) {
                $return = UpdateResponseFactory::success('有新版本');
                cache()->put('cms_update_version', $res['data'], 60 * 60 * 12);
                hook('Statistic', [
                    'moduleName' => 'System',
                    'action' => 'Check',
                    'identification' => 'cms',
                    'type' => 'cms',
                ]);
            }
        }

        return $return;
    }

    public function ensureAppMeta(string $cloudHost, array $all): ?array
    {
        $cached = $this->getCachedAppUpdate($all);
        if ((!$cached || empty($cached['version'])) && !empty($all['version'])) {
            $this->checkApp($cloudHost, $all);
            $cached = $this->getCachedAppUpdate($all);
        }

        if ((!$cached || empty($cached['version'])) && !empty($all['identification']) && !empty($all['cloudtype'])) {
            $cached = $this->fetchLatestAppMetaFromCloudList($cloudHost, $all);
            $this->cacheAppMeta($all, $cached ?: []);
            $cached = $this->getCachedAppUpdate($all);
        }

        return (!$cached || empty($cached['version'])) ? null : $cached;
    }

    public function cacheAppMeta(array $all, array $package): void
    {
        $normalized = $this->normalizeAppMeta($all, $package);
        if (!$normalized) {
            return;
        }

        cache()->put($this->getAppUpdateCacheKey($normalized), $normalized, 60 * 60 * 12);
        $this->syncUpdateLimit($normalized, $normalized);
    }

    public function ensureCmsMeta(string $cloudHost, string $currentVersion): ?array
    {
        $cached = cache()->get('cms_update_version');
        if ((!is_array($cached) || empty($cached['version']))) {
            $this->checkCms($cloudHost, $currentVersion);
            $cached = cache()->get('cms_update_version');
        }

        return (!is_array($cached) || empty($cached['version'])) ? null : $cached;
    }

    public function resolveAppBlockReason(string $cloudHost, array $all): ?string
    {
        $versionLimit = session()->get('versionLimit') ?: [];
        $key = $this->getVersionLimitKey($all);
        if (!empty($versionLimit[$key])) {
            $existing = trim((string) $versionLimit[$key]);
            if ($existing !== '' && mb_strpos($existing, '主程序版本太低') === false) {
                return $existing;
            }
        }

        $cached = $this->ensureAppMeta($cloudHost, $all);
        $versionLimit = session()->get('versionLimit') ?: [];
        if (!empty($versionLimit[$key])) {
            return $versionLimit[$key];
        }

        if (!$cached) {
            return '更新包信息不存在，请先检查版本';
        }

        $manifest = PackageManifest::normalize($cached, $all['cloudtype'], $all['identification']);
        $blocked = CompatibilityChecker::checkInstallable($manifest);
        $this->syncUpdateLimit($all, $cached);
        return $blocked;
    }

    public function resolveCmsBlockReason(string $cloudHost, string $currentVersion): ?string
    {
        $cached = $this->ensureCmsMeta($cloudHost, $currentVersion);
        if (!$cached || empty($cached['version'])) {
            return '主程序更新包信息不存在，请先检查版本';
        }

        if ($currentVersion !== '' && version_compare($cached['version'], $currentVersion) <= 0) {
            return '当前已是最新版本';
        }

        return null;
    }

    public function clearVersionLimit(array $all): void
    {
        $versionLimit = session()->get('versionLimit') ?: [];
        unset($versionLimit[$this->getVersionLimitKey($all)]);
        session()->put('versionLimit', $versionLimit);
    }

    private function syncUpdateLimit(array $all, array $remoteData): void
    {
        $key = $this->getVersionLimitKey($all);
        $versionLimit = session()->get('versionLimit') ?: [];
        unset($versionLimit[$key]);

        if (empty($remoteData)) {
            session()->put('versionLimit', $versionLimit);
            return;
        }

        $manifest = PackageManifest::normalize($remoteData, $all['cloudtype'], $all['identification']);
        $blocked = CompatibilityChecker::checkInstallable($manifest);
        if ($blocked) {
            $versionLimit[$key] = $blocked;
        }

        session()->put('versionLimit', $versionLimit);
    }

    private function getVersionLimitKey(array $all): string
    {
        return $all['cloudtype'] . '_' . $all['identification'];
    }

    private function getAppUpdateCacheKey(array $all): string
    {
        return 'app_update_' . $all['cloudtype'] . '_' . $all['identification'];
    }

    private function getCachedAppUpdate(array $all): ?array
    {
        $cached = cache()->get($this->getAppUpdateCacheKey($all));
        return is_array($cached) ? $cached : null;
    }

    private function fetchLatestAppMetaFromCloudList(string $cloudHost, array $all): ?array
    {
        $params = [
            'cloudtype' => $all['cloudtype'] ?? '',
            'action' => 'get-app-list',
            'page' => 1,
            'limit' => 100,
            'search' => $all['identification'] ?? '',
            'order' => 'id',
            'by' => 'desc',
        ];

        $res = curl_request($cloudHost . '/api/cloud/applist?' . http_build_query($params));
        $res = json_decode($res, true);
        if (($res['status'] ?? 0) != 200) {
            return null;
        }

        $list = $res['data']['list']['data'] ?? [];
        if (!is_array($list) || empty($list)) {
            return null;
        }

        $targetIdentification = strtolower(trim((string) ($all['identification'] ?? '')));
        if ($targetIdentification === '') {
            return null;
        }

        foreach ($list as $item) {
            if (!is_array($item)) {
                continue;
            }
            $itemIdentification = strtolower(trim((string) ($item['identification'] ?? '')));
            if ($itemIdentification === $targetIdentification) {
                return $item;
            }
        }

        return null;
    }

    private function normalizeAppMeta(array $all, array $package): ?array
    {
        if (empty($package) || !is_array($package)) {
            return null;
        }

        $cloudType = trim((string) ($all['cloudtype'] ?? $package['cloudtype'] ?? $package['cloud_type'] ?? ''));
        $identification = trim((string) ($all['identification'] ?? $package['identification'] ?? ''));
        if ($cloudType === '' || $identification === '') {
            return null;
        }

        $normalized = $package;
        $normalized['cloudtype'] = $cloudType;
        $normalized['identification'] = $identification;

        if (empty($normalized['size']) && !empty($normalized['sourcesize'])) {
            $normalized['size'] = intval($normalized['sourcesize']);
        }

        return empty($normalized['version']) ? null : $normalized;
    }
}
