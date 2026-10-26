<?php

namespace App\Support\Update;

/**
 * 更新包结构校验。
 *
 * 单包在线安装/更新时，更新包会被直接解压到 plugins/ 或 modules/ 根目录，且解压过程不剥离任何前缀。
 * 因此包内文件应以 "<包目录名>/..." 组织（例如 DeployAgent/Config/config.php）；如果直接是
 * Config/config.php 散落在包根目录，文件会落到 plugins/modules 根目录甚至覆盖其它包，造成目录污染。
 * 这里在解压前把结构校验掉（只校验包目录层，不带 modules/ 或 plugins/ 前缀）。
 */
class PackageLayoutValidator
{
    /**
     * 允许的顶层根目录（小写）。
     */
    const ALLOWED_ROOTS = ['plugins', 'modules'];

    /**
     * 校验单包结构。
     *
     * @param \PclZip $zip 已打开的更新包
     * @param string $expectedRoot 期望的顶层目录：plugins / modules
     * @param string $expectedDirectory 期望的包目录名（manifest 的 directory）
     */
    public static function validate(\PclZip $zip, string $expectedRoot, string $expectedDirectory): array
    {
        $expectedRoot = strtolower(trim($expectedRoot));
        $expectedDirectory = trim($expectedDirectory);
        if ($expectedRoot === '' || $expectedDirectory === '') {
            return self::fail('package_layout_context_missing', '包结构校验缺少上下文，已阻止解压');
        }

        if (!in_array($expectedRoot, self::ALLOWED_ROOTS, true)) {
            return self::fail('package_layout_root_invalid', '不支持的包类型：' . $expectedRoot);
        }

        $list = $zip->listContent();
        if (!is_array($list) || $list === []) {
            return self::fail('package_layout_empty', '更新包为空或无法读取，已阻止解压');
        }

        // 实际解压目标为 modules/plugins 根目录，且解压过程不剥离任何前缀
        // （UpdateUnzipService::detectExtractRemovePath 对非主题返回空、callback_pre_extract 不改路径），
        // 因此包内文件应以 "<directory>/" 直接组织（如 DeployAgent/Config/config.php），
        // 而非 "modules/<directory>/..."，否则会双重嵌套到 modules/modules/<directory>。
        // 这里只校验包目录这一层，防止文件散落到根目录覆盖其它包。
        $expectedPrefix = strtolower($expectedDirectory) . '/';
        $hasPayload = false;
        $outOfScope = [];
        $traversal = [];

        foreach ($list as $entry) {
            $filename = str_replace('\\', '/', trim((string) ($entry['filename'] ?? ''), '/'));
            if ($filename === '') {
                continue;
            }

            if (strpos($filename, '..') !== false && self::hasTraversal($filename)) {
                $traversal[] = $filename;
                continue;
            }

            if (!empty($entry['folder']) || substr($filename, -1) === '/') {
                continue;
            }

            $hasPayload = true;
            if (strpos(strtolower($filename), $expectedPrefix) !== 0) {
                $outOfScope[] = $filename;
            }
        }

        if (!empty($traversal)) {
            return self::fail(
                'package_layout_traversal',
                '更新包含非法路径（..），已阻止解压：' . self::preview($traversal)
            );
        }

        if (!$hasPayload) {
            return self::fail('package_layout_empty', '更新包内没有任何文件，已阻止解压');
        }

        if (!empty($outOfScope)) {
            return self::fail(
                'package_layout_mismatch',
                '更新包结构与目标目录不一致（期望 ' . $expectedDirectory
                . '/... 组织，不应带 modules/ 或 plugins/ 前缀），已阻止解压，避免覆盖到其它包：'
                . self::preview($outOfScope)
            );
        }

        return ['status' => 200];
    }

    private static function hasTraversal(string $filename): bool
    {
        foreach (explode('/', $filename) as $segment) {
            if ($segment === '..') {
                return true;
            }
        }

        return false;
    }

    private static function preview(array $items): string
    {
        $items = array_slice(array_values(array_unique($items)), 0, 3);
        $text = implode('、', $items);
        if (mb_strlen($text) > 200) {
            $text = mb_substr($text, 0, 200) . '…';
        }

        return $text;
    }

    private static function fail(string $reasonCode, string $message): array
    {
        return [
            'status' => 500,
            'msg' => $message,
            'reason_code' => $reasonCode,
        ];
    }
}
