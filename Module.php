<?php

namespace MediaText;

use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Acl;

class Module extends AbstractModule
{
    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, 'MediaText\Api\Adapter\RepresentationAdapter');
        $acl->allow(null, 'MediaText\Entity\Representation', 'read');
        $acl->allow(
            [Acl::ROLE_EDITOR, Acl::ROLE_REVIEWER, Acl::ROLE_AUTHOR],
            'MediaText\Entity\Representation',
            ['create', 'update', 'delete']
        );

        $acl->allow(
            [Acl::ROLE_EDITOR, Acl::ROLE_REVIEWER, Acl::ROLE_AUTHOR],
            'MediaText\Controller\Admin\MediaRepresentation'
        );
        $acl->allow(
            [Acl::ROLE_EDITOR, Acl::ROLE_REVIEWER, Acl::ROLE_AUTHOR],
            'MediaText\Controller\Admin\Representation'
        );

        // Make mediatext representation visible only if the corresponding media is visible
        // This also works when the module Group is enabled
        $em = $this->getServiceLocator()->get('Omeka\EntityManager');
        $filters = $em->getFilters();
        if ($filters->isEnabled('resource_visibility')) {
            $filter = $filters->getFilter('resource_visibility');
            $filter->addRelatedEntity('MediaText\Entity\Representation', 'media_id');
        }
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');

        $connection->executeStatement(<<<SQL
            CREATE TABLE mediatext_representation (
                id INT AUTO_INCREMENT NOT NULL,
                media_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                type VARCHAR(255) NOT NULL,
                format VARCHAR(255) NOT NULL,
                compressed_content LONGBLOB NOT NULL,
                created DATETIME NOT NULL,
                modified DATETIME NOT NULL,
                INDEX IDX_69EC30BEEA9FDD75 (media_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $connection->executeStatement(<<<SQL
            ALTER TABLE mediatext_representation
            ADD CONSTRAINT FK_69EC30BEEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE
        SQL);
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');

        $connection->executeStatement('DROP TABLE mediatext_representation');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $settings = $services->get('Omeka\Settings');

        $form = $formElementManager->get(Form\ConfigForm::class);
        $form->setData([
            'mediatext_compression_level' => $settings->get('mediatext_compression_level'),
        ]);
        $form->prepare();

        return $renderer->formCollection($form);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $formElementManager = $services->get('FormElementManager');
        $settings = $services->get('Omeka\Settings');
        $form = $formElementManager->get(Form\ConfigForm::class);
        $form->setData($controller->params()->fromPost());
        if (!$form->isValid()) {
            $controller->messenger()->addFormErrors($form);
            return false;
        }

        $data = $form->getData();

        $start_compression_job = $data['mediatext_start_compression_job'];

        $settings->set('mediatext_compression_level', $data['mediatext_compression_level']);

        if ($start_compression_job) {
            $job = $controller->jobDispatcher()->dispatch(Job\UpdateCompression::class);
            $message = new \Omeka\Stdlib\Message(
                'Compression job started. %s', // @translate
                sprintf(
                    '<a href="%s">%s</a>',
                    $controller->url()->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()]),
                    $controller->translate('See job details'),
                )
            );
            $message->setEscapeHtml(false);
            $controller->messenger()->addSuccess($message);
        }

        return true;
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach('*', 'view.layout', [$this, 'onViewLayout']);

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.show.section_nav',
            [$this, 'onViewShowSectionNavForAdminMedia']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.show.after',
            [$this, 'onViewShowAfterForAdminMedia']
        );

        $sharedEventManager->attach(
            'Omeka\Api\Representation\MediaRepresentation',
            'rep.resource.json',
            [$this, 'onRepResourceJsonForMedia']
        );

        $controllers = [
            'Omeka\Controller\Admin\Item',
            'Omeka\Controller\Admin\Media',
        ];
        foreach ($controllers as $controller) {
            $sharedEventManager->attach(
                $controller,
                'view.advanced_search',
                [$this, 'onViewAdvancedSearch']
            );

            $sharedEventManager->attach(
                $controller,
                'view.search.filters',
                [$this, 'onViewSearchFilters']
            );
        }

        $apiAdapters = [
            'Omeka\Api\Adapter\ItemAdapter',
            'Omeka\Api\Adapter\MediaAdapter',
        ];
        foreach ($apiAdapters as $apiAdapter) {
            $sharedEventManager->attach(
                $apiAdapter,
                'api.search.query',
                [$this, 'onApiSearchQuery']
            );
        }

        $sharedEventManager->attach(
            'Solr\ValueExtractor\ItemValueExtractor',
            'solr.value_extractor.fields',
            [$this, 'onSolrValueExtractorFieldsForItemValueExtractor']
        );
        $sharedEventManager->attach(
            'Solr\ValueExtractor\ItemValueExtractor',
            'solr.value_extractor.extract_value',
            [$this, 'onSolrValueExtractorExtractValueForItemValueExtractor']
        );
    }

    public function onViewLayout(Event $event)
    {
        $view = $event->getTarget();

        if ($view->status()->isAdminRequest()) {
            $view->headLink()->appendStylesheet($view->assetUrl('css/mediatext-admin.css', 'MediaText'));
        } elseif ($view->status()->isSiteRequest()) {
            $view->headLink()->appendStylesheet($view->assetUrl('css/mediatext-site.css', 'MediaText'));
        }
    }

    public function onViewShowSectionNavForAdminMedia(Event $event)
    {
        $section_nav = $event->getParam('section_nav');
        $section_nav['text-representations'] = 'Text representations';
        $event->setParam('section_nav', $section_nav);
    }

    public function onViewShowAfterForAdminMedia(Event $event)
    {
        $serviceLocator = $this->getServiceLocator();
        $api = $serviceLocator->get('Omeka\ApiManager');

        $view = $event->getTarget();
        $media = $view->get('media');

        $representations = $api->search('mediatext_representations', ['media_id' => $media->id()])->getContent();

        echo $view->partial('media-text/admin/media/section-text-representations', ['media' => $media, 'representations' => $representations]);
    }

    public function onRepResourceJsonForMedia(Event $event)
    {
        $media = $event->getTarget();
        $jsonLd = $event->getParam('jsonLd');
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $representations = $api->search('mediatext_representations', ['media_id' => $media->id()])->getContent();
        if ($representations) {
            $jsonLd['o-module-mediatext:representations'] = array_map(fn ($r) => $r->getReference(), $representations);
            $event->setParam('jsonLd', $jsonLd);
        }
    }

    public function onViewAdvancedSearch(Event $event)
    {
        $partials = $event->getParam('partials');

        $partials[] = 'media-text/common/advanced-search/has-textual-representation';

        $event->setParam('partials', $partials);
    }

    public function onApiSearchQuery(Event $event)
    {
        $status = $this->getServiceLocator()->get('Omeka\Status');
        if (!$status->isAdminRequest()) {
            return;
        }

        $adapter = $event->getTarget();
        $qb = $event->getParam('queryBuilder');
        $request = $event->getParam('request');

        $hasTextualRepresentations = $request->getValue('mediatext_has_textual_representations', '');
        if ($hasTextualRepresentations !== '') {
            $subQb = $adapter->getEntityManager()->createQueryBuilder();
            if ($adapter instanceof \Omeka\Api\Adapter\MediaAdapter) {
                $subQb->select('r')
                    ->from('MediaText\Entity\Representation', 'r')
                    ->andWhere('r.media = omeka_root');
            } elseif ($adapter instanceof \Omeka\Api\Adapter\ItemAdapter) {
                $subQb->select('r')
                    ->from('MediaText\Entity\Representation', 'r')
                    ->join('r.media', 'm')
                    ->andWhere('m.item = omeka_root');
            } else {
                // Other adapters not supported
                return;
            }

            $expr = $qb->expr()->exists($subQb->getDQL());
            if (!$hasTextualRepresentations) {
                $expr = $qb->expr()->not($expr);
            }
            $qb->andWhere($expr);
        }
    }

    public function onViewSearchFilters(Event $event)
    {
        $view = $event->getTarget();
        $query = $event->getParam('query');
        $filters = $event->getParam('filters');

        $hasTextualRepresentations = $query['mediatext_has_textual_representations'] ?? '';
        if ($hasTextualRepresentations !== '') {
            $filters[$view->translate('Has textual representation')][] = $hasTextualRepresentations ? $view->translate('Yes') : $view->translate('No');
        }

        $event->setParam('filters', $filters);
    }

    public function onSolrValueExtractorFieldsForItemValueExtractor(Event $event)
    {
        $fields = $event->getParam('fields');

        $fields['media']['children']['mediatext_text_content'] = [
            'label' => 'Media text representation content (plain text)', // @translate
        ];

        $event->setParam('fields', $fields);
    }

    public function onSolrValueExtractorExtractValueForItemValueExtractor(Event $event)
    {
        $item = $event->getTarget();
        $field = $event->getParam('field');

        if ($field === 'media/mediatext_text_content') {
            $services = $this->getServiceLocator();
            $em = $services->get('Omeka\EntityManager');
            $apiAdapters = $services->get('Omeka\ApiAdapterManager');
            $mediatextRepresentationAdapter = $apiAdapters->get('mediatext_representations');

            $query = $em->createQuery('SELECT r FROM MediaText\Entity\Representation r JOIN r.media m WHERE m.item = :item_id');
            $query->setParameter('item_id', $item->id());
            $representations = $query->getResult();

            $texts = [];
            foreach ($representations as $representation) {
                $representationRepresentation = $mediatextRepresentationAdapter->getRepresentation($representation);
                // TODO Implement contentAsPlainText
                $texts[] = $representationRepresentation->contentAsPlainText();
                $em->detach($representation);
            }

            if (!empty($texts)) {
                $event->setParam('value', implode("\n", $texts));
            }
        }
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }
}
