<?php

namespace MediaText\Representation\Extractor;

use Omeka\ServiceManager\AbstractPluginManager;

class Manager extends AbstractPluginManager
{
    protected $autoAddInvokableClass = false;

    protected $instanceOf = RepresentationExtractorInterface::class;
}
