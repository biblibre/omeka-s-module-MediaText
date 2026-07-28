<?php
namespace MediaText\Service\Listener;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Listener\SolrSharedListenerAggregate;
use Psr\Container\ContainerInterface;

class SolrSharedListenerAggregateFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $entityManager = $services->get('Omeka\EntityManager');
        $apiAdapterManager = $services->get('Omeka\ApiAdapterManager');

        $service = new SolrSharedListenerAggregate($entityManager, $apiAdapterManager);

        return $service;
    }
}
