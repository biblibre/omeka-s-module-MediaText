<?php

namespace MediaText\Representation\Ingester;

use Laminas\View\Renderer\PhpRenderer;

abstract class AbstractRepresentationIngester implements RepresentationIngesterInterface
{
    public function form(PhpRenderer $view): string
    {
        $form = $view->partial('media-text/common/representation-ingester-form/title');
        $form .= $view->partial('media-text/common/representation-ingester-form/type');
        $form .= $view->partial('media-text/common/representation-ingester-form/format');

        return $form;
    }
}
