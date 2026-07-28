<?php

namespace MediaText\Representation\Ingester;

use Laminas\View\Renderer\PhpRenderer;
use MediaText\Entity\Representation;
use Omeka\Api\Request;
use Omeka\Stdlib\ErrorStore;

interface RepresentationIngesterInterface
{
    /**
     * Return the name of the ingester
     */
    public function getName(): string;

    /**
     * Return a human-readable label for the ingester
     */
    public function getLabel(): string;

    /**
     * Return HTML form elements to be added to the representation creation form
     */
    public function form(PhpRenderer $view): string;

    /**
     * Ingest a representation
     *
     * The implementation should call $representation->setContent() and
     * eventually $representation->setSource()
     */
    public function ingest(Representation $representation, Request $request, ErrorStore $errorStore): void;
}
