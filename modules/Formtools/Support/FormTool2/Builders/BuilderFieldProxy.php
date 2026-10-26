<?php

namespace Modules\Formtools\Support\FormTool2\Builders;

class BuilderFieldProxy
{
    private Builder $builder;
    private string $identification;

    public function __construct(Builder $builder, string $identification)
    {
        $this->builder = $builder;
        $this->identification = trim($identification);
    }

    public function set(string $attribute, $value): self
    {
        $this->builder->setFieldAttributes($this->identification, [
            $attribute => $value,
        ]);
        return $this;
    }

    public function name(string $value): self
    {
        return $this->set('name', $value);
    }

    public function placeholder(string $value): self
    {
        return $this->set('placeholder', $value);
    }

    public function value($value): self
    {
        return $this->set('value', $value);
    }

    public function datas(array $datas): self
    {
        return $this->set('datas', $datas);
    }

    public function required($value = true): self
    {
        return $this->set('required', $value);
    }

    public function notes(string $value): self
    {
        return $this->set('notes', $value);
    }

    public function formtype(string $value): self
    {
        return $this->set('formtype', $value);
    }

    public function disabled($value = true): self
    {
        return $this->set('disabled', $value);
    }

    public function cssClass($value): self
    {
        return $this->set('cssClass', $value);
    }

    public function rows(int $value): self
    {
        return $this->set('rows', $value);
    }

    public function width($value): self
    {
        return $this->set('width', $value);
    }

    public function row(): self
    {
        return $this->set('showtype', 'row');
    }

    public function column(): self
    {
        return $this->set('showtype', 'column');
    }

    public function end(): Builder
    {
        return $this->builder;
    }
}

