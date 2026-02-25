<?php
declare(strict_types=1);

namespace Capacitacion\InputFilter;

use Laminas\InputFilter\InputFilter;

final class CapacitacionInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(['name'=>'title','required'=>true]);
        $this->add(['name'=>'alias','required'=>true]);
        $this->add(['name'=>'description','required'=>false]);
        $this->add(['name'=>'media_type','required'=>true]);
        $this->add(['name'=>'media_url','required'=>true]);
        $this->add(['name'=>'thumbnail','required'=>false]);
        $this->add(['name'=>'expires_at','required'=>false]);
        $this->add(['name'=>'published','required'=>true]);
    }
}
