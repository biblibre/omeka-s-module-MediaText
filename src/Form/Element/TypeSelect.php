<?php
namespace MediaText\Form\Element;

use Laminas\Form\Element\Select;

class TypeSelect extends Select
{
    public function getValueOptions(): array
    {
        $valueOptions = [
            'transcription' => 'Transcription', // @translate
            'transliteration' => 'Transliteration', // @translate
            'translation' => 'Translation', // @translate
            'other' => 'Other', // @translate
        ];

        return $valueOptions;
    }
}
