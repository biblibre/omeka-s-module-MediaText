<?php

namespace MediaText;

use DateTime;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use MediaText\Entity\Representation;
use Omeka\Api\Exception;
use Omeka\Api\Request;
use Omeka\Entity\Media;
use Omeka\Module\AbstractModule;
use Omeka\Permissions\Acl;
use Omeka\Stdlib\ErrorStore;

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

        $em->getFilters()->enable('mediatext_representation_visibility');
        $em->getFilters()->getFilter('mediatext_representation_visibility')->setServiceLocator($this->getServiceLocator());
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
                is_public TINYINT(1) NOT NULL,
                is_extracted TINYINT(1) NOT NULL,
                position INT NOT NULL,
                source LONGTEXT DEFAULT NULL,
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

        if (version_compare($oldVersion, '0.2.0', '<')) {
            $connection->executeStatement(<<<'SQL'
                ALTER TABLE mediatext_representation ADD is_public TINYINT(1) NOT NULL AFTER format
            SQL);
            $connection->executeStatement(<<<'SQL'
                ALTER TABLE mediatext_representation ADD is_extracted TINYINT(1) NOT NULL AFTER is_public
            SQL);
            $connection->executeStatement(<<<'SQL'
                ALTER TABLE mediatext_representation ADD position INT NOT NULL AFTER is_extracted
            SQL);
            $connection->executeStatement(<<<'SQL'
                ALTER TABLE mediatext_representation ADD source LONGTEXT DEFAULT NULL AFTER position
            SQL);
        }
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
            'Omeka\Controller\Admin\Media',
            'view.edit.section_nav',
            [$this, 'onViewEditSectionNavForAdminMedia']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.edit.before',
            [$this, 'onViewEditBeforeForAdminMedia']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.edit.form.after',
            [$this, 'onViewEditFormAfterForAdminMedia']
        );

        $sharedEventManager->attach(
            'Omeka\Api\Adapter\MediaAdapter',
            'api.hydrate.post',
            [$this, 'onApiHydratePostForMedia']
        );
        $sharedEventManager->attach(
            'Omeka\Api\Adapter\ItemAdapter',
            'api.hydrate.post',
            [$this, 'onApiHydratePostForItem']
        );

        $sharedEventManager->attach(
            'Omeka\Form\ResourceBatchUpdateForm',
            'form.add_elements',
            [$this, 'onFormAddElementsForResourceBatchUpdateForm'],
        );
        $sharedEventManager->attach(
            'Omeka\Form\ResourceBatchUpdateForm',
            'form.add_input_filters',
            [$this, 'onFormAddInputFiltersForResourceBatchUpdateForm'],
        );
        foreach (['Omeka\Api\Adapter\MediaAdapter', 'Omeka\Api\Adapter\ItemAdapter'] as $identifier) {
            $sharedEventManager->attach(
                $identifier,
                'api.preprocess_batch_update',
                [$this, 'onApiPreprocessBatchUpdate']
            );
        }

        $sharedEventManager->attach(
            'Omeka\Api\Representation\MediaRepresentation',
            'rep.resource.json',
            [$this, 'onRepResourceJsonForMedia']
        );

        $sharedListenerAggregateNames = [
            'MediaText\Listener\AdvancedSearchSharedListenerAggregate',
            'MediaText\Listener\SolrSharedListenerAggregate',
        ];

        $serviceLocator = $this->getServiceLocator();
        foreach ($sharedListenerAggregateNames as $sharedListenerAggregateName) {
            $solrSharedListenerAggregate = $serviceLocator->get($sharedListenerAggregateName);
            $solrSharedListenerAggregate->attachShared($sharedEventManager);
        }
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
        $view = $event->getTarget();
        $section_nav = $event->getParam('section_nav');
        $section_nav['textual-representations'] = $view->translate('Textual representations');
        $event->setParam('section_nav', $section_nav);
    }

    public function onViewShowAfterForAdminMedia(Event $event)
    {
        $serviceLocator = $this->getServiceLocator();
        $api = $serviceLocator->get('Omeka\ApiManager');

        $view = $event->getTarget();
        $media = $view->get('media');

        $representations = $api->search(
            'mediatext_representations',
            [
                'media_id' => $media->id(),
                'sort_by' => 'position',
                'sort_order' => 'asc',
            ]
        )->getContent();

        $values = [
            'media' => $media,
            'representations' => $representations,
        ];

        echo $view->partial('media-text/admin/media/section-textual-representations-show', $values);
    }

    public function onViewEditSectionNavForAdminMedia(Event $event)
    {
        $view = $event->getTarget();
        $section_nav = $event->getParam('section_nav');
        $section_nav['textual-representations'] = $view->translate('Textual representations');
        $event->setParam('section_nav', $section_nav);
    }

    public function onViewEditBeforeForAdminMedia(Event $event)
    {
        $view = $event->getTarget();
        $view->form->setAttribute('enctype', 'multipart/form-data');
    }

    public function onViewEditFormAfterForAdminMedia(Event $event)
    {
        $serviceLocator = $this->getServiceLocator();
        $api = $serviceLocator->get('Omeka\ApiManager');
        $representationIngesterManager = $serviceLocator->get('MediaText\RepresentationIngesterManager');
        $representationExtractorManager = $serviceLocator->get('MediaText\RepresentationExtractorManager');

        $view = $event->getTarget();
        $media = $view->get('media');

        $representations = $api->search(
            'mediatext_representations',
            [
                'media_id' => $media->id(),
                'sort_by' => 'position',
                'sort_order' => 'asc',
            ]
        )->getContent();

        $ingesters = [];
        $ingesterNames = $representationIngesterManager->getRegisteredNames(sortAlpha: true);
        foreach ($ingesterNames as $ingesterName) {
            $ingester = $representationIngesterManager->get($ingesterName);
            $ingesters[] = $ingester;
        }

        $extractors = [];
        $extractorNames = $representationExtractorManager->getRegisteredNames(sortAlpha: true);
        foreach ($extractorNames as $extractorName) {
            $extractor = $representationExtractorManager->get($extractorName);
            if ($extractor->canExtractFrom($media)) {
                $extractors[] = $extractor;
            }
        }

        echo $view->partial(
            'media-text/admin/media/section-textual-representations-edit',
            [
                'media' => $media,
                'representations' => $representations,
                'ingesters' => $ingesters,
                'extractors' => $extractors,
            ]
        );
    }

    public function onApiHydratePostForMedia(Event $event)
    {
        $adapter = $event->getTarget();

        $entity = $event->getParam('entity');
        $request = $event->getParam('request');
        $errorStore = $event->getParam('errorStore');

        $serviceLocator = $this->getServiceLocator();
        $em = $serviceLocator->get('Omeka\EntityManager');
        $translator = $serviceLocator->get('MvcTranslator');
        $representationExtractorManager = $serviceLocator->get('MediaText\RepresentationExtractorManager');

        $data = $request->getContent();
        $position = 1;

        if ($request->getOperation() === Request::UPDATE && $adapter->shouldHydrate($request, 'o:mediatext_representation')) {
            $representationsData = $request->getValue('o:mediatext_representation', []);
            $representationAdapter = $adapter->getAdapter('mediatext_representations');
            $entityClass = $representationAdapter->getEntityClass();
            $retainRepresentations = [];
            foreach ($representationsData as $representationData) {
                if (isset($representationData['o:id'])) {
                    $representation = $representationAdapter->findEntity($representationData['o:id']);
                    $representation->setPosition($position);
                    if (isset($representationData['o:is_public'])) {
                        $representation->setIsPublic((bool) $representationData['o:is_public']);
                    }
                    $retainRepresentations[] = $representation;
                } else {
                    // Create a new representation.
                    $representation = new $entityClass;
                    $representation->setMedia($entity);
                    $representation->setPosition($position);
                    $subrequest = new Request(Request::CREATE, 'mediatext_representations');
                    $subrequest->setContent($representationData);

                    // We have to retrieve file data like this because
                    // Omeka\Controller\Admin\MediaController::editAction does
                    // not pass file data to the api
                    $fileData = $serviceLocator->get('Application')->getRequest()->getFiles()->toArray();
                    $subrequest->setFileData($fileData);

                    try {
                        $representationAdapter->hydrateEntity($subrequest, $representation, new ErrorStore);
                        $em->persist($representation);
                    } catch (Exception\ValidationException $e) {
                        $errorStore->mergeErrors($e->getErrorStore(), 'o:mediatext_representation');
                    }

                    $retainRepresentations[] = $representation;
                }

                $position++;
            }

            $representations = $em->getRepository(Representation::class)->findBy(['media' => $entity->getId()]);
            foreach ($representations as $representation) {
                $inArray = false;
                foreach ($retainRepresentations as $retainRepresentation) {
                    if ($retainRepresentation->getId() === $representation->getId()) {
                        $inArray = true;
                        break;
                    }
                }

                if (!$inArray) {
                    $em->remove($representation);
                }
            }
        } else {
            $position = 1 + $em->getRepository(Representation::class)->count(['media' => $entity->getId()]);
        }

        $removeRepresentations = $request->getValue('mediatext_remove_representations', '');
        if ($removeRepresentations === 'all' || $removeRepresentations === 'extracted') {
            $position = 1;
            $representations = $em->getRepository(Representation::class)->findBy(['media' => $entity->getId()], ['position' => 'ASC']);
            foreach ($representations as $representation) {
                if ($removeRepresentations === 'all' || ($removeRepresentations === 'extracted' && $representation->getIsExtracted())) {
                    $em->remove($representation);
                } else {
                    $representation->setPosition($position++);
                }
            }
        }

        if ($request->getOperation() === Request::CREATE) {
            $extractorNames = $representationExtractorManager->getRegisteredNames();
        } else {
            $extractorNames = $request->getValue('mediatext_extractors', []);
        }
        $extractedRepresentations = $this->extractRepresentationsFromMedia($entity, $extractorNames);
        foreach ($extractedRepresentations as $extractedRepresentation) {
            $extractedRepresentation->setPosition($position++);
            $em->persist($extractedRepresentation);
        }
    }

    public function onApiHydratePostForItem(Event $event)
    {
        $adapter = $event->getTarget();

        $entity = $event->getParam('entity');
        $request = $event->getParam('request');

        if ($request->getOperation() !== Request::UPDATE) {
            return;
        }

        $serviceLocator = $this->getServiceLocator();
        $em = $serviceLocator->get('Omeka\EntityManager');
        $translator = $serviceLocator->get('MvcTranslator');
        $representationExtractorManager = $serviceLocator->get('MediaText\RepresentationExtractorManager');

        $data = $request->getContent();
        $representationRepository = $em->getRepository(Representation::class);

        $nextPosition = [];

        $removeRepresentations = $request->getValue('mediatext_remove_representations', '');
        if ($removeRepresentations === 'all' || $removeRepresentations === 'extracted') {
            foreach ($entity->getMedia() as $media) {
                $position = 1;
                $representations = $representationRepository->findBy(['media' => $media->getId()], ['position' => 'ASC']);
                foreach ($representations as $representation) {
                    if ($removeRepresentations === 'all' || ($removeRepresentations === 'extracted' && $representation->getIsExtracted())) {
                        $em->remove($representation);
                    } else {
                        $representation->setPosition($position++);
                    }
                }

                $nextPosition[$media->getId()] = $position;
            }
        }

        $extractorNames = $request->getValue('mediatext_extractors', []);
        foreach ($entity->getMedia() as $media) {
            $position = $nextPosition[$media->getId()] ?? (1 + $representationRepository->count(['media' => $media->getId()]));
            $extractedRepresentations = $this->extractRepresentationsFromMedia($media, $extractorNames);
            foreach ($extractedRepresentations as $extractedRepresentation) {
                $extractedRepresentation->setPosition($position++);
                $em->persist($extractedRepresentation);
            }
        }
    }

    public function onFormAddElementsForResourceBatchUpdateForm(Event $event)
    {
        $form = $event->getTarget();

        $resourceType = $form->getOption('resource_type');
        if (!in_array($resourceType, ['item', 'media'], true)) {
            return;
        }

        $groups = $form->getOption('element_groups');
        $groups['mediatext'] = 'Media Text'; // @translate
        $form->setOption('element_groups', $groups);

        $form->add([
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'mediatext_remove_representations',
            'options' => [
                'element_group' => 'mediatext',
                'label' => 'Remove textual representation', // @translate
                'empty_option' => 'Do not remove representations', // @translate
                'value_options' => [
                    'all' => 'Remove all representations', // @translate
                    'extracted' => 'Remove automatically extracted representations only', // @translate
                ],
            ],
            'attributes' => [
                'class' => 'chosen-select',
            ],
        ]);

        $extractors = [];
        $representationExtractorManager = $this->getServiceLocator()->get('MediaText\RepresentationExtractorManager');
        $extractorNames = $representationExtractorManager->getRegisteredNames(sortAlpha: true);
        foreach ($extractorNames as $extractorName) {
            $extractor = $representationExtractorManager->get($extractorName);
            if ($extractor->isAvailable()) {
                $extractors[$extractorName] = $extractor->getLabel();
            }
        }

        if ($extractors) {
            $form->add([
                'type' => 'Laminas\Form\Element\Select',
                'name' => 'mediatext_extractors',
                'options' => [
                    'element_group' => 'mediatext',
                    'label' => 'Extract textual representation', // @translate
                    'value_options' => $extractors,
                ],
                'attributes' => [
                    'multiple' => true,
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Select extractors', // @translate
                ],
            ]);
        }
    }

    public function onFormAddInputFiltersForResourceBatchUpdateForm(Event $event)
    {
        $form = $event->getTarget();

        $resourceType = $form->getOption('resource_type');
        if (!in_array($resourceType, ['item', 'media'], true)) {
            return;
        }

        $inputFilter = $event->getParam('inputFilter');

        $inputFilter->add([
            'name' => 'mediatext_remove_representations',
            'required' => false,
        ]);
        $inputFilter->add([
            'name' => 'mediatext_extractors',
            'required' => false,
        ]);
    }

    public function onApiPreprocessBatchUpdate(Event $event)
    {
        $data = $event->getParam('data');
        $request = $event->getParam('request');
        $rawData = $request->getContent();

        if (isset($rawData['mediatext_remove_representations'])) {
            $data['mediatext_remove_representations'] = $rawData['mediatext_remove_representations'];
        }
        if (isset($rawData['mediatext_extractors'])) {
            $data['mediatext_extractors'] = $rawData['mediatext_extractors'];
        }

        $event->setParam('data', $data);
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

    protected function extractRepresentationsFromMedia(Media $media, array $extractorNames): array
    {
        $serviceLocator = $this->getServiceLocator();
        $representationExtractorManager = $serviceLocator->get('MediaText\RepresentationExtractorManager');
        $apiAdapterManager = $serviceLocator->get('Omeka\ApiAdapterManager');
        $translator = $serviceLocator->get('MvcTranslator');

        $adapter = $apiAdapterManager->get('media');
        $mediaRepresentation = $adapter->getRepresentation($media);
        $compressionLevel = (int) $serviceLocator->get('Omeka\Settings')->get('mediatext_compression_level', '-1');

        $representations = [];

        foreach ($extractorNames as $extractorName) {
            $extractor = $representationExtractorManager->get($extractorName);
            if ($extractor->isAvailable() && $extractor->canExtractFrom($mediaRepresentation)) {
                $extractedRepresentations = $extractor->getRepresentations($mediaRepresentation);
                foreach ($extractedRepresentations as $extractedRepresentation) {
                    $extractedRepresentation->setMedia($media);

                    $extractedRepresentation->setIsExtracted(true);

                    $title = $extractedRepresentation->getTitle();
                    if ($title === null || $title === '') {
                        $title = sprintf($translator->translate('Extracted by %s'), $extractor->getLabel());
                        $extractedRepresentation->setTitle($title);
                    }

                    if (null !== ($content = $extractedRepresentation->getContent())) {
                        $extractedRepresentation->setCompressedContent(gzencode($content, $compressionLevel));
                        $extractedRepresentation->setContent(null);
                    }

                    $extractedRepresentation->setCreated(new DateTime('now'));
                    $extractedRepresentation->setModified(new DateTime('now'));

                    $representations[] = $extractedRepresentation;
                }
            }
        }

        return $representations;
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }
}
