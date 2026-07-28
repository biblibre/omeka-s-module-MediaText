<?php

namespace MediaText\Service\Representation\Ingester;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Representation\Ingester\Upload;
use Psr\Container\ContainerInterface;

class UploadFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        return new Upload($services->get('Omeka\File\Uploader'));
    }
}
