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
        ],
    ],
    'mediatext_format_adapters' => [
        'invokables' => [
            'alto' => Format\Adapter\AltoAdapter::class,
            'plain_text' => Format\Adapter\PlainTextAdapter::class,
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
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
];
