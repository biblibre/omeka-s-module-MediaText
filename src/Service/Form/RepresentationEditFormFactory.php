<?php

namespace MediaText\Service\Form;

use MediaText\Form\RepresentationEditForm;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RepresentationEditFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $formatAdapterManager = $services->get('MediaText\FormatAdapterManager');

        $form = new RepresentationEditForm(null, $options ?? []);
        $form->setFormatAdapterManager($formatAdapterManager);

        return $form;
    }
}
