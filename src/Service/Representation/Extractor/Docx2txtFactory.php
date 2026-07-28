<?php

namespace MediaText\Service\Representation\Extractor;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Representation\Extractor\Docx2txt;
use Psr\Container\ContainerInterface;

class Docx2txtFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $cli = $services->get('Omeka\Cli');
        $downloader = $services->get('Omeka\File\Downloader');
        $fileStore = $services->get('Omeka\File\Store');

        $service = new Docx2txt($cli);
        $service->setDownloader($downloader);
        $service->setFileStore($fileStore);

        return $service;
    }
}
