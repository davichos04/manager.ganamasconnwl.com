<?php
declare(strict_types=1);

namespace Capacitacion\InputFilter;

use Laminas\InputFilter\InputFilter;

final class QuestionInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(['name'=>'quiz_id','required'=>true]);
        $this->add(['name'=>'question_text','required'=>true]);
        $this->add(['name'=>'type','required'=>true]);
        $this->add(['name'=>'ordering','required'=>false]);
        $this->add(['name'=>'published','required'=>true]);
        $this->add(['name'=>'image','required'=>false]);
    }
}
