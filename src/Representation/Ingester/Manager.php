<?php

namespace MediaText\Representation\Ingester;

use Omeka\ServiceManager\AbstractPluginManager;

class Manager extends AbstractPluginManager
{
    protected $autoAddInvokableClass = false;

    protected $instanceOf = RepresentationIngesterInterface::class;
}
