<?php

namespace MediaText\Service\Form;

use MediaText\Form\RepresentationForm;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RepresentationFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $formatAdapterManager = $services->get('MediaText\FormatAdapterManager');

        $form = new RepresentationForm(null, $options ?? []);
        $form->setFormatAdapterManager($formatAdapterManager);

        return $form;
    }
}
