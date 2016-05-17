<?php

namespace LemoGrid\Column;

use Zend\Stdlib\AbstractOptions;

class TextOptions extends AbstractOptions
{
    /**
     * @var array
     */
    protected $textToReplace;

    /**
     * @param  array $textToReplace
     * @return $this
     */
    public function setTextToReplace(array $textToReplace)
    {
        $this->textToReplace = $textToReplace;

        return $this;
    }

    /**
     * @return array
     */
    public function getTextToReplace()
    {
        return $this->textToReplace;
    }
}
