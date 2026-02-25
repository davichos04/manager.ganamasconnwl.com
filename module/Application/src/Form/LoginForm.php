<?php

namespace Application\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter;

class LoginForm extends Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'loginForm');
        $this->add([
            'type' => Element\Email::class,
            'name' => 'email',
            'options' => [
                'label' => 'Usuario'
            ],
            'attributes' => [
                'required' => true,
                'id' => 'username',
                'data-toggle' => 'tooltip',
                'class' => 'form-control',
                'title' => 'Ingresa tu usuario',
                'placeholder' => 'Ingresa tu usuario'
            ],
        ]);

        $this->add([
            'type' => Element\Password::class,
            'name' => 'password',
            'options' => [
                'label' => 'Contraseña'
            ],
            'attributes' => [
                'required' => true,
                'id' => 'password',
                'data-toggle' => 'tooltip',
                'class' => 'form-control',
                'title' => 'Ingresa tu contraseña',
                'placeholder' => 'Ingresa tu contraseña'
            ],
        ]);

        $this->add([
            'type' => Element\Csrf::class,
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ],

        ]);

        $this->add([
            'type' => Element\Submit::class,
            'name' => 'account_login',
            'options' => [
                'label' => 'Contraseña'
            ],
            'attributes' => [
                'id' => 'loginButton',
                'value' => 'Iniciar sesión',
                'class' => 'btn-primary'
            ],
        ]);
    }
}