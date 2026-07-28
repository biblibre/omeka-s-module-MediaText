<?php

namespace MediaText\Representation\Ingester;

use Laminas\View\Renderer\PhpRenderer;
use MediaText\Entity\Representation;
use Omeka\Api\Request;
use Omeka\File\Uploader;
use Omeka\Stdlib\ErrorStore;

class Upload extends AbstractRepresentationIngester
{
    public function __construct(protected Uploader $uploader)
    {
    }

    public function getName(): string
    {
        return 'upload';
    }

    public function getLabel(): string
    {
        return 'Upload'; // @translate
    }

    public function ingest(Representation $representation, Request $request, ErrorStore $errorStore): void
    {
        $data = $request->getContent();
        $fileData = $request->getFileData();

        if (!isset($fileData['upload_file'])) {
            $errorStore->addError('error', 'No files were uploaded');
            return;
        }

        if (!isset($data['file_index'])) {
            $errorStore->addError('error', 'No file index was specified');
            return;
        }

        $index = $data['file_index'];
        if (!isset($fileData['upload_file'][$index])) {
            $errorStore->addError('error', 'No file uploaded for the specified index');
            return;
        }

        $tempFile = $this->uploader->upload($fileData['upload_file'][$index], $errorStore);
        if (!$tempFile) {
            return;
        }

        $content = file_get_contents($tempFile->getTempPath());
        $representation->setContent($content);

        if (!array_key_exists('o:source', $data)) {
            $representation->setSource($fileData['upload_file'][$index]['name']);
        }
    }

    public function form(PhpRenderer $view): string
    {
        $form = parent::form($view);

        $file = new \Laminas\Form\Element\File('upload_file[__index__]');
        $file->setLabel('Upload file'); // @translate
        $file->setAttribute('required', true);

        $fileIndex = new \Laminas\Form\Element\Hidden('o:mediatext_representation[__index__][file_index]');
        $fileIndex->setValue('__index__');

        $form .= $view->formRow($file);
        $form .= $view->formHidden($fileIndex);

        return $form;
    }
}
