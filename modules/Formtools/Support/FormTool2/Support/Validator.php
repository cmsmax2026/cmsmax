<?php

namespace Modules\Formtools\Support\FormTool2\Support;

class Validator
{
    public static function validatePageData(array $pageData): array
    {
        $errors = [];
        $fields = $pageData['fields'] ?? [];
        if (!is_array($fields)) {
            $errors[] = 'fields 必须是数组';
            $fields = [];
        }

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }
            $id = trim((string) ($field['identification'] ?? ''));
            if ($id === '') {
                $errors[] = '字段 identification 不能为空';
                continue;
            }
            $formtype = trim((string) ($field['formtype'] ?? 'text'));
            if ($formtype === '') {
                $errors[] = '字段 ' . $id . ' formtype 不能为空';
                continue;
            }

            if (in_array($formtype, ['select', 'selectMore', 'multipleSelect', 'radio', 'checkbox', 'checkboxList'], true)) {
                if (isset($field['datas']) && !is_array($field['datas'])) {
                    $errors[] = '字段 ' . $id . ' datas 必须是数组';
                }
            }
        }

        $searchFields = $pageData['searchFields'] ?? [];
        if ($searchFields !== null && !is_array($searchFields)) {
            $errors[] = 'searchFields 必须是数组';
        }

        $listActions = $pageData['listActions'] ?? [];
        if ($listActions !== null && !is_array($listActions)) {
            $errors[] = 'listActions 必须是数组';
        }

        return array_values(array_unique(array_filter($errors)));
    }
}

