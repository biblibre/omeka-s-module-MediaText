<?php

namespace MediaText\Format\Adapter;

use DOMDocument;
use DOMXPath;
use Omeka\Stdlib\ErrorStore;

class AltoAdapter extends AbstractFormatAdapter
{
    public function getName(): string
    {
        return 'alto';
    }

    public function getLabel(): string
    {
        return 'ALTO';
    }

    public function validate(string $representation, ErrorStore $errorStore): void
    {
        if (trim($representation) === '') {
            $errorStore->addError('', 'Representation is empty'); // @translate
            return;
        }

        $document = new DOMDocument();

        libxml_use_internal_errors(true);
        if (false === $document->loadXML($representation)) {
            foreach (libxml_get_errors() as $error) {
                $errorStore->addError('', sprintf('Invalid XML: %s at line %d', $error->message, $error->line)); // @translate
            }
            libxml_clear_errors();

            return;
        }

        $this->validateDOMDocument($document, $errorStore);
    }

    protected function validateDOMDocument(DOMDocument $document, ErrorStore $errorStore): void
    {
        $rootElement = $document->documentElement;
        if ($rootElement->tagName !== 'alto') {
            $errorStore->addError('', 'The root XML element is not alto'); // @translate
            return;
        }

        $altoNamespaceUri = $rootElement->namespaceURI;
        if (!$altoNamespaceUri) {
            $errorStore->addError('', 'The root XML element does not have a namespace'); // @translate
            return;
        }
    }

    public function getPlainText(string $representation): string
    {
        $document = new DOMDocument();

        libxml_use_internal_errors(true);
        if (false === $document->loadXML($representation)) {
            return '';
        }

        $errorStore = new ErrorStore();
        $this->validateDOMDocument($document, $errorStore);
        if ($errorStore->hasErrors()) {
            return '';
        }

        $xpath = new DOMXPath($document);

        $rootElement = $document->documentElement;
        $altoNamespaceUri = $rootElement->namespaceURI;
        $xpath->registerNamespace('alto', $altoNamespaceUri);

        $textBlocks = [];
        $textBlockNodes = $xpath->query('//alto:TextBlock');
        foreach ($textBlockNodes as $textBlockNode) {
            $textLines = [];
            $textLineNodes = $xpath->query('alto:TextLine', $textBlockNode);
            foreach ($textLineNodes as $textLineNode) {
                $strings = [];
                $stringNodes = $xpath->query('alto:String', $textLineNode);
                foreach ($stringNodes as $stringNode) {
                    $strings[] = $stringNode->getAttribute('CONTENT');
                }
                $textLines[] = implode(' ', $strings);
            }
            $textBlocks[] = implode("\n", $textLines);
        }

        $text = implode("\n\n", $textBlocks);

        return $text;
    }
}
