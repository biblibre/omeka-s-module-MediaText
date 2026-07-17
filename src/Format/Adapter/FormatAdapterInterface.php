<?php

namespace MediaText\Format\Adapter;

use Omeka\ServiceManager\SortableInterface;
use Omeka\Stdlib\ErrorStore;

interface FormatAdapterInterface extends SortableInterface
{
    /**
     * Return the name of the format adapter
     */
    public function getName(): string;

    /**
     * Return a human-readable label for the format
     */
    public function getLabel(): string;

    /**
     * Validate a representation.
     *
     * Set validation errors to the passed $errorStore object. If an error is
     * set the representation will not be stored.
     */
    public function validate(string $representation, ErrorStore $errorStore): void;

    /**
     * Return the passed representation as plain text
     */
    public function getPlainText(string $representation): string;
}
