<?php

namespace MediaText\Controller\Admin;

use MediaText\Form\RepresentationEditForm;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;

class RepresentationController extends AbstractActionController
{
    public function showAction()
    {
        $id = $this->params()->fromRoute('id');
        $representation = $this->api()->read('mediatext_representations', $id)->getContent();

        $view = new ViewModel;
        $view->setVariable('representation', $representation);

        return $view;
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        $representation = $this->api()->read('mediatext_representations', $id)->getContent();

        $form = $this->getForm(RepresentationEditForm::class);
        $form->setData([
            'o:title' => $representation->title(),
            'o:type' => $representation->type(),
            'o:format' => $representation->format(),
            'o:content' => $representation->content(),
        ]);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $formData = $form->getData();
                $data = [
                    'o:title' => $formData['o:title'],
                    'o:type' => $formData['o:type'],
                    'o:format' => $formData['o:format'],
                    'o:content' => $formData['o:content'],
                ];

                $response = $this->api($form)->update('mediatext_representations', $representation->id(), $data, [], ['isPartial' => true]);
                if ($response) {
                    $this->messenger()->addSuccess('Text representation updated'); // @translate

                    return $this->redirect()->toRoute('admin/media-text/representation-id', ['id' => $representation->id()]);
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('representation', $representation);

        return $view;
    }

    public function deleteConfirmAction()
    {
        $representation = $this->api()->read('mediatext_representations', $this->params('id'))->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resource', $representation);
        $view->setVariable('resourceLabel', 'text representation'); // @translate
        $view->setVariable('partialPath', 'media-text/admin/representation/show-details'); // @translate

        return $view;
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');

        $representation = $this->api()->read('mediatext_representations', $id)->getContent();

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('mediatext_representations', $representation->id());
                if ($response) {
                    $this->messenger()->addSuccess('Text representation deleted'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        return $this->redirect()->toRoute(
            'admin/id',
            ['controller' => 'media', 'action' => 'show', 'id' => $representation->media()->id()],
            ['fragment' => 'text-representations'],
        );
    }

    public function showDetailsAction()
    {
        $id = $this->params()->fromRoute('id');
        $representation = $this->api()->read('mediatext_representations', $id)->getContent();

        $view = new ViewModel;
        $view->setVariable('resource', $representation);
        $view->setTerminal(true);

        return $view;
    }
}
