<?php

namespace Modules\Formtools\Support\FormTool2\Dto;

class PagePayload
{
    private array $pageData = [];

    public function __construct(array $pageData = [])
    {
        $this->pageData = $pageData;
    }

    public static function fromArray(array $pageData): self
    {
        return new self($pageData);
    }

    public function toArray(): array
    {
        return $this->pageData;
    }
}

