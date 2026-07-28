<?php

namespace MediaText\Service\Representation\Extractor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Representation\Extractor\Odt2txt;
use Psr\Container\ContainerInterface;

class Odt2txtFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $cli = $services->get('Omeka\Cli');
        $downloader = $services->get('Omeka\File\Downloader');
        $fileStore = $services->get('Omeka\File\Store');

        $service = new Odt2txt($cli);
        $service->setDownloader($downloader);
        $service->setFileStore($fileStore);

        return $service;
    }
}
