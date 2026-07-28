<?php

namespace MediaText;

return [
    'api_adapters' => [
        'invokables' => [
            'mediatext_representations' => Api\Adapter\RepresentationAdapter::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'MediaText\Controller\Admin\Representation' => Controller\Admin\RepresentationController::class,
        ],
        'factories' => [
            'MediaText\Controller\Admin\MediaRepresentation' => Service\Controller\Admin\MediaRepresentationControllerFactory::class,
        ],
    ],
    'entity_manager' => [
        'filters' => [
            'mediatext_representation_visibility' => Db\Filter\RepresentationVisibilityFilter::class,
        ],
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
    ],
    'form_elements' => [
        'factories' => [
            'MediaText\Form\RepresentationForm' => Service\Form\RepresentationFormFactory::class,
            'MediaText\Form\RepresentationEditForm' => Service\Form\RepresentationEditFormFactory::class,
            'MediaText\Form\Element\FormatSelect' => Service\Form\Element\FormatSelectFactory::class,
        ],
        'invokables' => [
            'MediaText\Form\Element\TypeSelect' => Form\Element\TypeSelect::class,
        ],
    ],
    'mediatext_format_adapters' => [
        'invokables' => [
            'alto' => Format\Adapter\AltoAdapter::class,
            'plain_text' => Format\Adapter\PlainTextAdapter::class,
        ],
    ],
    'mediatext_representation_ingesters' => [
        'invokables' => [
            'textarea' => Representation\Ingester\Textarea::class,
        ],
        'factories' => [
            'upload' => Service\Representation\Ingester\UploadFactory::class,
        ],
    ],
    'mediatext_representation_extractors' => [
        'factories' => [
            'pdftotext' => Service\Representation\Extractor\PdftotextFactory::class,
            'docx2txt' => Service\Representation\Extractor\Docx2txtFactory::class,
            'odt2txt' => Service\Representation\Extractor\Odt2txtFactory::class,
            'catdoc' => Service\Representation\Extractor\CatdocFactory::class,
            'tesseract' => Service\Representation\Extractor\TesseractFactory::class,
        ],
    ],
    'resource_page_block_layouts' => [
        'invokables' => [
            'mediatextRepresentationsAsPlainText' => Site\ResourcePageBlockLayout\RepresentationsAsPlainText::class,
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'media-text' => [
                        'type' => \Laminas\Router\Http\Literal::class,
                        'options' => [
                            'route' => '/media-text',
                            'defaults' => [
                                '__NAMESPACE__' => 'MediaText\Controller\Admin',
                            ],
                        ],
                        'child_routes' => [
                            'representation' => [
                                'type' => \Laminas\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '/representation[/:action]',
                                    'defaults' => [
                                        'controller' => 'representation',
                                        'action' => 'browse',
                                    ],
                                ],
                            ],
                            'representation-id' => [
                                'type' => \Laminas\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '/representation/:id[/:action]',
                                    'defaults' => [
                                        'controller' => 'representation',
                                        'action' => 'show',
                                    ],
                                ],
                            ],
                            'media-representation' => [
                                'type' => \Laminas\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '/media/:media-id/representation[/:action]',
                                    'defaults' => [
                                        'controller' => 'media-representation',
                                        'action' => 'browse',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'MediaText\FormatAdapterManager' => Service\Format\Adapter\ManagerFactory::class,
            'MediaText\Listener\SolrSharedListenerAggregate' => Service\Listener\SolrSharedListenerAggregateFactory::class,
            'MediaText\RepresentationIngesterManager' => Service\Representation\Ingester\ManagerFactory::class,
            'MediaText\RepresentationExtractorManager' => Service\Representation\Extractor\ManagerFactory::class,
        ],
        'invokables' => [
            'MediaText\Listener\AdvancedSearchSharedListenerAggregate' => Listener\AdvancedSearchSharedListenerAggregate::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'mediatextFormatSelect' => Service\View\Helper\FormatSelectFactory::class,
            'mediatextTypeSelect' => Service\View\Helper\TypeSelectFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
];
