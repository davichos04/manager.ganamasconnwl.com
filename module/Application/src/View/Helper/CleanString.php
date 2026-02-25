<?php
namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class CleanString extends AbstractHelper
{
    public function __invoke($text): string
    {
        return mb_convert_case(str_replace(',', '', preg_replace('/[[:cntrl:]]/', '', $text)), MB_CASE_TITLE, 'UTF-8');
    }
}
