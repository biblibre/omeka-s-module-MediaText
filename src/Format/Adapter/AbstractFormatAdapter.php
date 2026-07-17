<?php

namespace MediaText\Format\Adapter;

abstract class AbstractFormatAdapter implements FormatAdapterInterface
{
    public function getSortableString()
    {
        return $this->getLabel();
    }
}
