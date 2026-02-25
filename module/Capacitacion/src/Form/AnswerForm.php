<?php
declare(strict_types=1);

namespace Capacitacion\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Capacitacion\InputFilter\AnswerInputFilter;

final class AnswerForm extends Form
{
    public function __construct()
    {
        parent::__construct('answer');

        $this->add(['name'=>'id','type'=>Element\Hidden::class]);
        $this->add(['name'=>'question_id','type'=>Element\Hidden::class]);

        $this->add(['name'=>'answer_text','type'=>Element\Textarea::class,'options'=>['label'=>'Respuesta']]);
        $this->add(['name'=>'is_correct','type'=>Element\Checkbox::class,'options'=>['label'=>'Es correcta']]);
        $this->add(['name'=>'submit','type'=>Element\Submit::class,'attributes'=>['value'=>'Guardar Respuesta']]);

        $this->setInputFilter(new AnswerInputFilter());

        $this->get('answer_text')->setAttribute('class','form-control')->setAttribute('rows','2');
        $this->get('is_correct')->setAttribute('class','custom-control-input');
        $this->get('submit')->setAttribute('class','btn btn-primary');
    }
}
