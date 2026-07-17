<?php

namespace MediaText\Form;

use Laminas\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'mediatext_compression_level',
            'type' => 'Laminas\Form\Element\Select',
            'options' => [
                'label' => 'Text compression level', // @translate
                'info' => 'Media Text compresses text to reduce the space taken in database. This setting allows to change the compression level.', // @translate
                'value_options' => [
                    '-1' => 'Default compression level', // @translate
                    '0' => '0 (no compression)', // @translate
                    '1' => '1 (fastest)', // @translate
                    '2' => '2', // @translate
                    '3' => '3', // @translate
                    '4' => '4', // @translate
                    '5' => '5', // @translate
                    '6' => '6', // @translate
                    '7' => '7', // @translate
                    '8' => '8', // @translate
                    '9' => '9 (best compression)', // @translate
                ],
            ],
        ]);

        $this->add([
            'name' => 'mediatext_start_compression_job',
            'type' => 'Laminas\Form\Element\Checkbox',
            'options' => [
                'label' => 'Start compression background job', // @translate
                'info' => 'Start background job to recompress mediatext documents', // @translate
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
        ]);
    }
}
