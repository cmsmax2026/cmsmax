<?php

namespace Modules\Formtools\Support\FormTool2\Dto;

class FieldDefinition
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

