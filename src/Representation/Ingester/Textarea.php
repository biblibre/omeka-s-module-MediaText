<?php

namespace MediaText\Representation\Ingester;

use Laminas\View\Renderer\PhpRenderer;
use MediaText\Entity\Representation;
use Omeka\Api\Request;
use Omeka\Stdlib\ErrorStore;

class Textarea extends AbstractRepresentationIngester
{
    public function getName(): string
    {
        return 'textarea';
    }

    public function getLabel(): string
    {
        return 'Text area'; // @translate
    }

    public function ingest(Representation $representation, Request $request, ErrorStore $errorStore): void
    {
        $data = $request->getContent();

        if (!isset($data['content'])) {
            $errorStore->addError('error', 'No content was specified');
            return;
        }

        $representation->setContent($data['content']);
    }

    public function form(PhpRenderer $view): string
    {
        $form = parent::form($view);

        $content = new \Laminas\Form\Element\Textarea('o:mediatext_representation[__index__][content]');
        $content->setLabel('Content'); // @translate
        $content->setAttribute('rows', '10');

        $form .= $view->formRow($content);

        return $form;
    }
}
