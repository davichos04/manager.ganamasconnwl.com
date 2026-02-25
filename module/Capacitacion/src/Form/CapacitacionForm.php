<?php

declare(strict_types=1);

namespace Capacitacion\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Capacitacion\InputFilter\CapacitacionInputFilter;

final class CapacitacionForm extends Form
{
    public function __construct()
    {
        parent::__construct('capacitacion');

        $this->add(['name' => 'id', 'type' => Element\Hidden::class]);

        $this->add(['name' => 'title', 'type' => Element\Text::class, 'options' => ['label' => 'Título']]);
        $this->add(['name' => 'alias', 'type' => Element\Text::class, 'options' => ['label' => 'Alias (URL friendly)']]);
        $this->add(['name' => 'description', 'type' => Element\Textarea::class, 'options' => ['label' => 'Descripción']]);
        $this->add([
            'name' => 'media_type',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Tipo de media',
                'value_options' => ['video' => 'Video', 'image' => 'Imagen', 'pdf' => 'PDF']
            ],
        ]);
        //$this->add(['name' => 'media_url', 'type' => Element\Text::class, 'options' => ['label' => 'URL media']]);
        $this->add([
            'name' => 'media_url',
            'type' => \Laminas\Form\Element\File::class,
            'options' => [
                'label' => 'Archivo (PDF/Imagen)',
            ],
            'attributes' => [
                'class' => 'custom-file-input',
            ],
        ]);

        $this->add([
            'name' => 'expires_at',
            'type' => \Laminas\Form\Element\DateTimeLocal::class,
            'options' => [
                'label' => 'Expira',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'published',
            'type' => \Laminas\Form\Element\Checkbox::class,
            'options' => [
                'label' => 'Publicado',
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
        ]);
        $this->add([
            'name' => 'thumbnail',
            'type' => \Laminas\Form\Element\File::class,
            'options' => [
                'label' => 'Thumbnail (opcional)',
            ],
            'attributes' => [
                'class' => 'custom-file-input',
            ],
        ]);
        $this->add(['name' => 'submit', 'type' => Element\Submit::class, 'attributes' => ['value' => 'Guardar']]);

        $this->setInputFilter(new CapacitacionInputFilter());

        foreach (['title', 'alias', 'media_url', 'thumbnail', 'expires_at'] as $n) {
            $this->get($n)->setAttribute('class', 'form-control');
        }
        $this->get('description')->setAttribute('class', 'form-control')->setAttribute('rows', '4');
        $this->get('media_url')->setAttribute('class', 'custom-select');
        $this->get('published')->setAttribute('class', 'custom-control-input');
        $this->get('submit')->setAttribute('class', 'btn btn-primary');
    }
}
