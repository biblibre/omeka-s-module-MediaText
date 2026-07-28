<?php

namespace MediaText\Service\Representation\Extractor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Representation\Extractor\Pdftotext;
use Psr\Container\ContainerInterface;

class PdftotextFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $cli = $services->get('Omeka\Cli');
        $downloader = $services->get('Omeka\File\Downloader');
        $fileStore = $services->get('Omeka\File\Store');

        $service = new Pdftotext($cli);
        $service->setDownloader($downloader);
        $service->setFileStore($fileStore);

        return $service;
    }
}
