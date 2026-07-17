<?php

namespace MediaText\Service\Format\Adapter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Format\Adapter\Manager;
use Psr\Container\ContainerInterface;

class ManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $config = $services->get('Config');

        return new Manager($services, $config['mediatext_format_adapters']);
    }
}
