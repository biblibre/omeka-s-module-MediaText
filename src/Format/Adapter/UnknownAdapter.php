<?php

namespace MediaText\Format\Adapter;

use Omeka\Stdlib\ErrorStore;

class UnknownAdapter extends AbstractFormatAdapter
{
    public function __construct(protected string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return sprintf('[Unknown: %s]', $this->name);
    }

    public function validate(string $representation, ErrorStore $errorStore): void
    {
        $errorStore->addError('', sprintf('This representation format (%s) is unknown', $this->name));
    }

    public function getPlainText(string $representation): string
    {
        return '';
    }
}
