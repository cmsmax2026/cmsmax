<?php

namespace Modules\Formtools\Support\FormTool2\Support;

class TreeOptions
{
    public static function fromRows(array $rows, string $idKey = 'id', string $pidKey = 'pid', string $labelKey = 'name'): array
    {
        $nodes = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = (int) ($row[$idKey] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $nodes[$id] = [
                'value' => $id,
                'name' => (string) ($row[$labelKey] ?? ('#' . $id)),
                'pid' => (int) ($row[$pidKey] ?? 0),
                'children' => [],
            ];
        }

        $roots = [];
        foreach ($nodes as $id => $node) {
            $pid = (int) ($node['pid'] ?? 0);
            if ($pid > 0 && $pid !== $id && isset($nodes[$pid])) {
                $nodes[$pid]['children'][] = &$nodes[$id];
            } else {
                $roots[] = &$nodes[$id];
            }
        }

        return self::sortTree($roots);
    }

    public static function flatten(array $tree, string $prefixUnit = '— '): array
    {
        $flat = [];
        $walk = function ($node, int $depth) use (&$walk, &$flat, $prefixUnit) {
            if (!is_array($node)) {
                return;
            }
            $value = $node['value'] ?? '';
            $name = (string) ($node['name'] ?? '');
            $flat[] = [
                'value' => $value,
                'name' => ($depth > 0 ? str_repeat($prefixUnit, $depth) : '') . $name,
                'depth' => $depth,
            ];
            foreach (($node['children'] ?? []) as $child) {
                $walk($child, $depth + 1);
            }
        };
        foreach ($tree as $node) {
            $walk($node, 0);
        }
        return $flat;
    }

    private static function sortTree(array $tree): array
    {
        $sort = function (&$nodes) use (&$sort) {
            usort($nodes, function ($a, $b) {
                $aValue = (int) ($a['value'] ?? 0);
                $bValue = (int) ($b['value'] ?? 0);
                return $bValue <=> $aValue;
            });
            foreach ($nodes as &$node) {
                if (!empty($node['children']) && is_array($node['children'])) {
                    $sort($node['children']);
                }
            }
        };
        $sort($tree);
        return $tree;
    }
}

