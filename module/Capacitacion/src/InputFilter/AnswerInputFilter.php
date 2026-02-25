<?php
declare(strict_types=1);

namespace Capacitacion\InputFilter;

use Laminas\InputFilter\InputFilter;

final class AnswerInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(['name'=>'question_id','required'=>true]);
        $this->add(['name'=>'answer_text','required'=>true]);
        $this->add(['name'=>'is_correct','required'=>false]);
    }
}
