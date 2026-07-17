<?php
namespace MediaText\Site\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Site\ResourcePageBlockLayout\ResourcePageBlockLayoutInterface;
use Laminas\View\Renderer\PhpRenderer;

class RepresentationsAsPlainText implements ResourcePageBlockLayoutInterface
{
    public function getLabel(): string
    {
        return 'Textual representations as plain text'; // @translate
    }

    public function getCompatibleResourceNames(): array
    {
        return ['media'];
    }

    public function render(PhpRenderer $view, AbstractResourceEntityRepresentation $resource): string
    {
        return $view->partial('media-text/common/resource-page-block-layout/representations-as-plain-text', ['media' => $resource]);
    }
}
