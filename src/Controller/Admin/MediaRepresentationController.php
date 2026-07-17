<?php

namespace MediaText\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use MediaText\Form\RepresentationForm;
use MediaText\Format\Adapter\Manager as FormatAdapterManager;

class MediaRepresentationController extends AbstractActionController
{
    public function __construct(protected FormatAdapterManager $formatAdapterManager)
    {
    }

    public function addAction()
    {
        $mediaId = $this->params()->fromRoute('media-id');

        $media = $this->api()->read('media', $mediaId)->getContent();

        $form = $this->getForm(RepresentationForm::class);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $formData = $form->getData();
                $fileData = $this->getRequest()->getFiles()->toArray();
                $content = file_get_contents($fileData['file']['tmp_name']);

                $data = [
                    'o:media_id' => $media->id(),
                    'o:title' => $formData['title'],
                    'o:type' => $formData['type'],
                    'o:format' => $formData['format'],
                    'o:content' => $content,
                ];
                $response = $this->api($form)->create('mediatext_representations', $data);
                if ($response) {
                    $this->messenger()->addSuccess('Text representation added'); // @translate

                    return $this->redirect()->toRoute(
                        'admin/id',
                        ['controller' => 'media', 'action' => 'show', 'id' => $media->id()],
                        ['fragment' => 'text-representations']
                    );
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('media', $media);

        return $view;
    }
}
