<?php

namespace Modules\Formtools\Support\FormTool2\Support;

use Modules\Formtools\Support\FormTool2\Dto\ActionDefinition;
use Modules\Formtools\Support\FormTool2\Dto\FieldDefinition;
use Modules\Formtools\Support\FormTool2\Dto\SearchFieldDefinition;

class Normalizer
{
    public const FORM_TYPES = [
        "text", "textarea", "editor", "password", "radio", "checkbox", "checkboxList",
        "select", "selectMore", "multipleSelect", "number", "color",
        "upload", "uploadAjax", "image", "imageAjax",
        "date", "dateMonth", "dateYear", "time", "datetime", "dateRange", "datetimeRange",
        "hidden", "readonly", "word", "links", "legend", "button", "section", "switch",
        "json", "code", "tags"
    ];

    public const FORM_TYPE_ALIASES = [
        'file' => 'upload',
        'img' => 'image',
    ];

    public static function normalizeFormTypeName($formtype): string
    {
        $formtype = trim((string) $formtype);
        if ($formtype === '') {
            return 'text';
        }

        if (isset(self::FORM_TYPE_ALIASES[$formtype])) {
            $formtype = self::FORM_TYPE_ALIASES[$formtype];
        }

        return in_array($formtype, self::FORM_TYPES, true) ? $formtype : 'text';
    }

    public static function normalizeRequiredValue($value): string
    {
        if ($value === true) {
            return 'required';
        }

        if ($value === false || $value === null) {
            return '';
        }

        return (string) $value;
    }

    public static function normalizeColumnWidth($value): int
    {
        $width = intval($value);
        if ($width <= 0) {
            return 0;
        }
        return min(12, $width);
    }

    public static function normalizeFieldDefinition(array $field): array
    {
        $identification = trim((string) ($field['identification'] ?? ''));
        $showtype = trim((string) ($field['showtype'] ?? 'row'));
        $width = self::normalizeColumnWidth($field['width'] ?? 0);
        if (!in_array($showtype, ['row', 'column'], true)) {
            $showtype = 'row';
        }
        if ($width > 0) {
            $showtype = 'column';
        }

        return FieldDefinition::fromArray([
            'identification' => $identification,
            'name' => $field['name'] ?? '',
            'value' => $field['value'] ?? '',
            'formtype' => self::normalizeFormTypeName($field['formtype'] ?? 'text'),
            'datas' => is_array($field['datas'] ?? null) ? $field['datas'] : [],
            'notes' => (string) ($field['notes'] ?? ''),
            'required' => self::normalizeRequiredValue($field['required'] ?? ''),
            'placeholder' => (string) ($field['placeholder'] ?? ''),
            'rule' => (string) ($field['rule'] ?? 'string'),
            'regex' => (string) ($field['regex'] ?? ''),
            'maxlength' => max(0, intval($field['maxlength'] ?? 0)),
            'disabled' => $field['disabled'] ?? '',
            'cssClass' => $field['cssClass'] ?? '',
            'callback' => $field['callback'] ?? null,
            'jsfunction' => (string) ($field['jsfunction'] ?? ''),
            'aInfo' => is_array($field['aInfo'] ?? null) ? $field['aInfo'] : '',
            'showtype' => $showtype,
            'width' => $width,
            'template' => (string) ($field['template'] ?? ''),
            'relate' => $field['relate'] ?? '',
            'relateaction' => $field['relateaction'] ?? '',
            'actionby' => $field['actionby'] ?? '',
            'rows' => max(1, intval($field['rows'] ?? 5)),
        ])->toArray();
    }

    public static function normalizeSearchFieldDefinition(array $field): array
    {
        $formtype = self::normalizeFormTypeName($field['formtype'] ?? 'text');
        $searchView = 'formtools::admin.formsearchtemplates.' . $formtype;
        if (!view()->exists($searchView)) {
            $formtype = 'text';
        }

        return SearchFieldDefinition::fromArray([
            'identification' => trim((string) ($field['identification'] ?? '')),
            'placeholder' => (string) ($field['placeholder'] ?? ''),
            'value' => $field['value'] ?? '',
            'formtype' => $formtype,
            'datas' => is_array($field['datas'] ?? null) ? $field['datas'] : [],
        ])->toArray();
    }

    public static function normalizeActionDefinition(array $action, string $defaultCss = 'btn-default'): array
    {
        return ActionDefinition::fromArray([
            'actionName' => (string) ($action['actionName'] ?? ''),
            'actionUrl' => (string) ($action['actionUrl'] ?? ''),
            'cssClass' => (string) ($action['cssClass'] ?? $defaultCss),
            'param' => is_array($action['param'] ?? null) ? $action['param'] : [],
            'confirm' => $action['confirm'] ?? false,
            'popup' => (bool) ($action['popup'] ?? false),
            'target' => (string) ($action['target'] ?? ''),
            'isMoreSelect' => (bool) ($action['isMoreSelect'] ?? false),
            'noNeedId' => (bool) ($action['noNeedId'] ?? false),
            'show' => is_array($action['show'] ?? null) ? $action['show'] : [],
            'notIdArray' => is_array($action['notIdArray'] ?? null) ? array_values($action['notIdArray']) : [],
            'actionType' => (string) ($action['actionType'] ?? 'link'),
            'titleField' => (string) ($action['titleField'] ?? 'id'),
            'field' => (string) ($action['field'] ?? ''),
        ])->toArray();
    }
}

