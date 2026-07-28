<?php
namespace MediaText\View\Helper;

use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\View\Helper\AbstractHelper;

class FormatSelect extends AbstractHelper
{
    public function __construct(protected FormElementManager $formElementManager)
    {
    }

    public function __invoke(array $spec = [])
    {
        $spec['type'] = 'MediaText\Form\Element\FormatSelect';
        if (!isset($spec['options']['empty_option'])) {
            $spec['options']['empty_option'] = 'Select format…'; // @translate
        }

        $factory = new Factory($this->formElementManager);
        $element = $factory->createElement($spec);

        return $this->getView()->formSelectSort($element);
    }
}
