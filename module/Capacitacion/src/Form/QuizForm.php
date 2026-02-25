<?php
declare(strict_types=1);

namespace Capacitacion\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Capacitacion\InputFilter\QuizInputFilter;

final class QuizForm extends Form
{
    public function __construct()
    {
        parent::__construct('quiz');

        $this->add(['name'=>'id','type'=>Element\Hidden::class]);
        $this->add(['name'=>'capacitacion_id','type'=>Element\Hidden::class]);

        $this->add(['name'=>'title','type'=>Element\Text::class,'options'=>['label'=>'Título del Quiz']]);
        $this->add(['name'=>'max_attempts','type'=>Element\Text::class,'options'=>['label'=>'Max intentos']]);
        $this->add(['name'=>'pass_score','type'=>Element\Text::class,'options'=>['label'=>'Score para pasar (%)']]);
        $this->add(['name'=>'published','type'=>Element\Checkbox::class,'options'=>['label'=>'Publicado']]);
        $this->add([
            'name'=>'reward_mode',
            'type'=>Element\Select::class,
            'options'=>[
                'label'=>'Tipo de premio',
                'value_options'=>[
                    'none'=>'Ninguno',
                    'points'=>'Puntos',
                    'product'=>'E-Rewards (Producto)',
                ]
            ],
        ]);
        // IMPORTANT: reward_product_id is a select, populated by QuizFormFactory
        $this->add([
            'name'=>'reward_product_id',
            'type'=>Element\Select::class,
            'options'=>['label'=>'Producto e-reward'],
        ]);

        $this->add(['name'=>'reward_points','type'=>Element\Text::class,'options'=>['label'=>'Puntos (si aplica)']]);
        $this->add(['name'=>'reward_limit','type'=>Element\Text::class,'options'=>['label'=>'Límite de premios']]);
        $this->add(['name'=>'submit','type'=>Element\Submit::class,'attributes'=>['value'=>'Guardar Quiz']]);

        $this->setInputFilter(new QuizInputFilter());

        // Bootstrap 4 classes
        foreach (['title','max_attempts','pass_score','reward_points','reward_limit'] as $n) {
            $this->get($n)->setAttribute('class','form-control');
        }
        $this->get('reward_mode')->setAttribute('class','custom-select');
        $this->get('reward_product_id')->setAttribute('class','custom-select select2');
        $this->get('published')->setAttribute('class','custom-control-input');
        $this->get('reward_points')->setAttribute('inputmode','numeric');
        $this->get('reward_limit')->setAttribute('inputmode','numeric');
        $this->get('max_attempts')->setAttribute('inputmode','numeric');
        $this->get('pass_score')->setAttribute('inputmode','numeric');
        $this->get('submit')->setAttribute('class','btn btn-primary');
    }
}
