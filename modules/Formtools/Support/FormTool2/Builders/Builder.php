<?php

namespace Modules\Formtools\Support\FormTool2\Builders;

use Modules\Formtools\Support\FormTool2\Dto\PagePayload;
use Modules\Formtools\Support\FormTool2\Support\Normalizer;
use Modules\Formtools\Support\FormTool2\Support\Validator;
use Modules\Formtools\Support\FormTool2\Support\ViewResolver;

class Builder
{
    private array $pageData = [];
    private array $fieldsRaw = [];
    private array $fields = [];
    private array $searchFieldsRaw = [];
    private array $searchFields = [];
    private array $listActionsRaw = [];
    private array $listActions = [];
    private array $leftListActionsRaw = [];
    private array $leftListActions = [];
    private $datas = null;
    private ?string $currentField = null;

    public static function create(array $pageData = []): self
    {
        $builder = new self();
        $builder->pageData = $pageData;
        return $builder;
    }

    public function set(string $key, $value): self
    {
        $this->pageData[$key] = $value;
        return $this;
    }

    public function merge(array $data): self
    {
        $this->pageData = array_merge($this->pageData, $data);
        return $this;
    }

    public function field(string $identification, array $attributes = []): self
    {
        $identification = trim($identification);
        if ($identification === '') {
            throw new \InvalidArgumentException('identification 不能为空');
        }

        $this->fieldsRaw[$identification] = array_merge($this->fieldsRaw[$identification] ?? [], $attributes, [
            'identification' => $identification,
        ]);
        $this->fields[$identification] = Normalizer::normalizeFieldDefinition($this->fieldsRaw[$identification]);
        $this->currentField = $identification;
        return $this;
    }

    public function text(string $identification, string $name, $value = '', array $attributes = []): self
    {
        return $this->field($identification, array_merge($attributes, [
            'name' => $name,
            'value' => $value,
            'formtype' => 'text',
        ]));
    }

    public function textarea(string $identification, string $name, $value = '', array $attributes = []): self
    {
        return $this->field($identification, array_merge($attributes, [
            'name' => $name,
            'value' => $value,
            'formtype' => 'textarea',
        ]));
    }

    public function select(string $identification, string $name, $value = '', array $datas = [], array $attributes = []): self
    {
        return $this->field($identification, array_merge($attributes, [
            'name' => $name,
            'value' => $value,
            'formtype' => 'select',
            'datas' => $datas,
        ]));
    }

    public function hasField(string $identification): bool
    {
        $identification = trim($identification);
        return $identification !== '' && isset($this->fields[$identification]);
    }

    public function on(string $identification): BuilderFieldProxy
    {
        if (!$this->hasField($identification)) {
            throw new \InvalidArgumentException('字段不存在：' . $identification);
        }
        return new BuilderFieldProxy($this, $identification);
    }

    public function setFieldAttributes(string $identification, array $attributes): self
    {
        $identification = trim($identification);
        if ($identification === '') {
            return $this;
        }
        if (!isset($this->fieldsRaw[$identification])) {
            $this->fieldsRaw[$identification] = [
                'identification' => $identification,
            ];
        }
        $this->fieldsRaw[$identification] = array_merge($this->fieldsRaw[$identification], $attributes);
        $this->fields[$identification] = Normalizer::normalizeFieldDefinition($this->fieldsRaw[$identification]);
        $this->currentField = $identification;
        return $this;
    }

    public function searchField(string $identification, array $attributes = []): self
    {
        $identification = trim($identification);
        if ($identification === '') {
            throw new \InvalidArgumentException('identification 不能为空');
        }

        $this->searchFieldsRaw[$identification] = array_merge($this->searchFieldsRaw[$identification] ?? [], $attributes, [
            'identification' => $identification,
        ]);
        $this->searchFields[$identification] = Normalizer::normalizeSearchFieldDefinition($this->searchFieldsRaw[$identification]);
        return $this;
    }

    public function searchText(string $identification, string $placeholder = '', $value = ''): self
    {
        return $this->searchField($identification, [
            'formtype' => 'text',
            'placeholder' => $placeholder,
            'value' => $value,
        ]);
    }

    public function searchSelect(string $identification, array $datas = [], string $placeholder = '', $value = ''): self
    {
        return $this->searchField($identification, [
            'formtype' => 'select',
            'placeholder' => $placeholder,
            'value' => $value,
            'datas' => $datas,
        ]);
    }

    public function listActions(array $actions): self
    {
        $this->listActionsRaw = $actions;
        $this->listActions = [];
        foreach ($actions as $action) {
            if (!is_array($action)) {
                continue;
            }
            $this->listActions[] = Normalizer::normalizeActionDefinition($action);
        }
        return $this;
    }

    public function appendListAction(array $action): self
    {
        $this->listActionsRaw[] = $action;
        $this->listActions[] = Normalizer::normalizeActionDefinition($action);
        return $this;
    }

    public function leftListActions(array $groupsOrActions): self
    {
        $this->leftListActionsRaw = $groupsOrActions;
        $this->leftListActions = $this->normalizeLeftListActionGroups($groupsOrActions);
        return $this;
    }

    public function appendLeftListActionGroup(array $actions): self
    {
        $this->leftListActionsRaw[] = $actions;
        $this->leftListActions[] = $this->normalizeLeftActionGroup($actions);
        return $this;
    }

    public function datas($datas): self
    {
        $this->datas = $datas;
        return $this;
    }

    public function searchClearEmpty(string $url): self
    {
        $this->pageData['searchClearEmpty'] = $url;
        return $this;
    }

    public function isShowMoreCheckbox($value = true): self
    {
        $this->pageData['isShowMoreCheckbox'] = $value;
        return $this;
    }

    public function linkAppend(array $value = []): self
    {
        $this->pageData['linkAppend'] = $value;
        return $this;
    }

    public function toPageData(): array
    {
        $pageData = $this->pageData;
        if ($this->fields) {
            $pageData['fields'] = $this->fields;
        }
        if ($this->searchFields) {
            $pageData['searchFields'] = $this->searchFields;
        }
        if ($this->listActions) {
            $pageData['listActions'] = $this->listActions;
        }
        if ($this->leftListActions) {
            $pageData['leftListActions'] = $this->leftListActions;
        }
        if ($this->datas !== null && !array_key_exists('datas', $pageData)) {
            $pageData['datas'] = $this->datas;
        }
        $errors = Validator::validatePageData($pageData);
        if ($errors) {
            $pageData['builderErrors'] = $errors;
        }
        return PagePayload::fromArray($pageData)->toArray();
    }

    public function render(string $view, string $fallback = 'list')
    {
        $pageData = $this->toPageData();
        $moduleName = $pageData['moduleName'] ?? null;
        $resolved = ViewResolver::resolveAdminFormtoolView(is_string($moduleName) ? $moduleName : null, $view, $fallback);
        return view($resolved, compact('pageData'));
    }

    public function renderList($datas, string $view = 'list', string $fallback = 'list')
    {
        return $this->datas($datas)->render($view, $fallback);
    }

    public function renderTree($datas, string $view = 'listTree', string $fallback = 'listTree')
    {
        return $this->datas($datas)->render($view, $fallback);
    }

    private function normalizeLeftListActionGroups(array $datas): array
    {
        if (!$datas) {
            return [];
        }

        $isSingleGroup = isset($datas[0]) && is_array($datas[0]) && array_key_exists('actionName', $datas[0]);
        if ($isSingleGroup) {
            return [$this->normalizeLeftActionGroup($datas)];
        }

        $groups = [];
        foreach ($datas as $group) {
            if (!is_array($group)) {
                continue;
            }
            $groups[] = $this->normalizeLeftActionGroup($group);
        }

        return $groups;
    }

    private function normalizeLeftActionGroup(array $actions): array
    {
        $items = [];
        foreach ($actions as $action) {
            if (!is_array($action)) {
                continue;
            }
            $items[] = Normalizer::normalizeActionDefinition($action);
        }
        return array_values($items);
    }
}
