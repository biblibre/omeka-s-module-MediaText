<?php

namespace MediaText\Service\Representation\Extractor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Representation\Extractor\Tesseract;
use Psr\Container\ContainerInterface;

class TesseractFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $cli = $services->get('Omeka\Cli');
        $downloader = $services->get('Omeka\File\Downloader');
        $fileStore = $services->get('Omeka\File\Store');

        $service = new Tesseract($cli);
        $service->setDownloader($downloader);
        $service->setFileStore($fileStore);

        return $service;
    }
}
