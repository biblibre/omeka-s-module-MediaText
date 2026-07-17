<?php
namespace MediaText\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\File;
use MediaText\Format\Adapter\Manager as FormatAdapterManager;

class RepresentationForm extends Form
{
    protected FormatAdapterManager $formatAdapterManager;

    public function init()
    {
        $this->add([
            'name' => 'title',
            'type' => 'Laminas\Form\Element\Text',
            'options' => [
                'label' => 'Title', // @translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'type',
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
            'name' => 'format',
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
            'name' => 'file',
            'type' => File::class,
            'options' => [
                'label' => 'File', // @translate
            ],
            'attributes' => [
                'required' => true,
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
