<?php
namespace MediaText\Service\View\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\View\Helper\FormatSelect;
use Psr\Container\ContainerInterface;

class FormatSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        return new FormatSelect($services->get('FormElementManager'));
    }
}
