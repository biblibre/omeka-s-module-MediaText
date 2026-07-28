<?php
namespace MediaText\Service\Form\Element;

use Laminas\ServiceManager\Factory\FactoryInterface;
use MediaText\Form\Element\FormatSelect;
use Psr\Container\ContainerInterface;

class FormatSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $element = new FormatSelect;
        $element->setFormatAdapterManager($services->get('MediaText\FormatAdapterManager'));

        return $element;
    }
}
