<?php
declare(strict_types=1);

namespace Capacitacion\InputFilter;

use Laminas\InputFilter\InputFilter;

final class QuizInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(['name'=>'title','required'=>true]);
        $this->add(['name'=>'max_attempts','required'=>false]);
        $this->add(['name'=>'pass_score','required'=>false]);
        $this->add(['name'=>'published','required'=>true]);
        $this->add(['name'=>'reward_mode','required'=>true]);
        $this->add(['name'=>'reward_product_id','required'=>false]);
        $this->add(['name'=>'reward_points','required'=>false]);
        $this->add(['name'=>'reward_limit','required'=>true]);
    }
}
