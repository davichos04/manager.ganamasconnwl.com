<?php

namespace Application\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter;

class UploadForm extends Form
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        $this->addElements();
        $this->addInputFilter();
    }

    public function addElements()
    {
        // File Input
        $file = new Element\File('csv-file');
        $file->setLabel(' ');
        $file->setAttribute('id', 'csv-file');
        $file->setAttribute('multiple', false)
            ->setAttribute('required', true)
            ->setAttribute('class', 'form-control');
        $this->add($file);
    }

    public function addInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();

        // File Input
        $fileInput = new InputFilter\FileInput('csv-file');
        $fileInput->setRequired(true);

        // Define validators and filters as if only one file was being uploaded.
        // All files will be run through the same validators and filters
        // automatically.
        $fileInput->getValidatorChain()
            ->attachByName('filesize', ['max' => 204800])
            ->attachByName('filemimetype', ['mimeType' => 'text/csv', 'application/csv', 'text/plain']);

        // All files will be renamed, e.g.:
        //   ./data/tmpuploads/avatar_4b3403665fea6.png,
        //   ./data/tmpuploads/avatar_5c45147660fb7.png
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            [
                'target' => './data/uploads/upload.csv',
                'randomize' => true,
            ]
        );
        $inputFilter->add($fileInput);

        $this->setInputFilter($inputFilter);
    }
}