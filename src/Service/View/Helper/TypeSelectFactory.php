<?php
namespace MediaText\Service\View\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\View\Helper\TypeSelect;
use Psr\Container\ContainerInterface;

class TypeSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        return new TypeSelect($services->get('FormElementManager'));
    }
}
