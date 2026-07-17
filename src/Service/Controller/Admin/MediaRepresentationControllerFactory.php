<?php

namespace MediaText\Service\Controller\Admin;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Controller\Admin\MediaRepresentationController;
use Psr\Container\ContainerInterface;

class MediaRepresentationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $formatAdapterManager = $services->get('MediaText\FormatAdapterManager');

        $controller = new MediaRepresentationController($formatAdapterManager);

        return $controller;
    }
}
