<?php
namespace MediaText\Form\Element;

use Laminas\Form\Element\Select;
use MediaText\Format\Adapter\Manager as FormatAdapterManager;
use Omeka\Form\Element\SelectSortInterface;
use Omeka\Form\Element\SelectSortTrait;

class FormatSelect extends Select implements SelectSortInterface
{
    use SelectSortTrait;

    protected FormatAdapterManager $formatAdapterManager;

    public function getValueOptions(): array
    {
        $valueOptions = [];

        $formatAdapterManager = $this->getFormatAdapterManager();
        $formatAdapterNames = $formatAdapterManager->getRegisteredNames();
        foreach ($formatAdapterNames as $formatAdapterName) {
            $formatAdapter = $formatAdapterManager->get($formatAdapterName);
            $valueOptions[$formatAdapterName] = $formatAdapter->getLabel();
        }

        return $valueOptions;
    }

    public function setFormatAdapterManager(FormatAdapterManager $formatAdapterManager)
    {
        $this->formatAdapterManager = $formatAdapterManager;
    }

    public function getFormatAdapterManager(): FormatAdapterManager
    {
        return $this->formatAdapterManager;
    }
}
