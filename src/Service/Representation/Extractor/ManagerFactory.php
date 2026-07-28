<?php

namespace MediaText\Service\Representation\Extractor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Representation\Extractor\Manager;
use Psr\Container\ContainerInterface;

class ManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $config = $services->get('Config');

        return new Manager($services, $config['mediatext_representation_extractors']);
    }
}
