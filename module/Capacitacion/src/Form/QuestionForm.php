<?php
declare(strict_types=1);

namespace Capacitacion\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Capacitacion\InputFilter\QuestionInputFilter;

final class QuestionForm extends Form
{
    public function __construct()
    {
        parent::__construct('question');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(['name'=>'id','type'=>Element\Hidden::class]);
        $this->add(['name'=>'quiz_id','type'=>Element\Hidden::class]);

        $this->add(['name'=>'question_text','type'=>Element\Textarea::class,'options'=>['label'=>'Pregunta']]);
        $this->add([
            'name'=>'type',
            'type'=>Element\Select::class,
            'options'=>[
                'label'=>'Tipo de pregunta',
                'value_options'=>[
                    'radio'=>'Radio (1 correcta)',
                    'checkbox'=>'Checkbox (varias correctas)',
                    'input'=>'Input',
                    'ranking'=>'Ranking',
                    'open'=>'Abierta',
                    'comment'=>'Comentario',
                ],
            ],
        ]);
        $this->add(['name'=>'ordering','type'=>Element\Text::class,'options'=>['label'=>'Orden']]);
        $this->add(['name'=>'published','type'=>Element\Checkbox::class,'options'=>['label'=>'Publicado']]);
        $this->add(['name'=>'image','type'=>Element\File::class,'options'=>['label'=>'Imagen (máx 2MB)']]);
        $this->add(['name'=>'submit','type'=>Element\Submit::class,'attributes'=>['value'=>'Guardar Pregunta']]);

        $this->setInputFilter(new QuestionInputFilter());

        $this->get('question_text')->setAttribute('class','form-control')->setAttribute('rows','4');
        $this->get('type')->setAttribute('class','custom-select');
        $this->get('ordering')->setAttribute('class','form-control')->setAttribute('inputmode','numeric');
        $this->get('published')->setAttribute('class','custom-control-input');
        $this->get('image')->setAttribute('class','custom-file-input');
        $this->get('submit')->setAttribute('class','btn btn-primary');
    }
}
