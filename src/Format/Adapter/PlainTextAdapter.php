<?php

namespace MediaText\Format\Adapter;

use Omeka\Stdlib\ErrorStore;

class PlainTextAdapter extends AbstractFormatAdapter
{
    public function getName(): string
    {
        return 'plain_text';
    }

    public function getLabel(): string
    {
        return 'Plain text';
    }

    public function validate(string $representation, ErrorStore $errorStore): void
    {
    }

    public function getPlainText(string $representation): string
    {
        return $representation;
    }
}
