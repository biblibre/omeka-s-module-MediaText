<?php
namespace MediaText\Form;

use Laminas\Form\Form;
use MediaText\Format\Adapter\Manager as FormatAdapterManager;

class RepresentationEditForm extends Form
{
    protected FormatAdapterManager $formatAdapterManager;

    public function init()
    {
        $this->add([
            'name' => 'o:title',
            'type' => 'Laminas\Form\Element\Text',
            'options' => [
                'label' => 'Title', // @translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'o:type',
            'type' => 'Laminas\Form\Element\Select',
            'options' => [
                'label' => 'Type', // @translate
                'value_options' => [
                    'transcription' => 'Transcription', // @translate
                    'transliteration' => 'Transliteration', // @translate
                    'translation' => 'Translation', // @translate
                    'other' => 'Other', // @translate
                ],
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'o:format',
            'type' => 'Laminas\Form\Element\Select',
            'options' => [
                'label' => 'Format', // @translate
                'value_options' => $this->getFormatValueOptions(),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'o:content',
            'type' => 'Laminas\Form\Element\Textarea',
            'options' => [
                'label' => 'Content', // @translate
            ],
            'attributes' => [
                'required' => true,
                'rows' => '20',
            ],
        ]);
    }

    protected function getFormatValueOptions(): array
    {
        $valueOptions = [];

        $formatAdapterManager = $this->getFormatAdapterManager();
        $formatAdapterNames = $formatAdapterManager->getRegisteredNames(sortAlpha: true);
        foreach ($formatAdapterNames as $formatAdapterName) {
            $formatAdapter = $formatAdapterManager->get($formatAdapterName);
            $valueOptions[$formatAdapterName] = $formatAdapter->getLabel();
        }

        return $valueOptions;
    }

    public function setFormatAdapterManager(FormatAdapterManager $formatAdapterManager): void
    {
        $this->formatAdapterManager = $formatAdapterManager;
    }

    public function getFormatAdapterManager(): FormatAdapterManager
    {
        return $this->formatAdapterManager;
    }
}
