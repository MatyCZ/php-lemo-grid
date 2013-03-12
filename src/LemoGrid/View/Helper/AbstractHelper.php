<?php

namespace LemoGrid\View\Helper;

use LemoGrid\GridInterface;
use Zend\I18n\View\Helper\AbstractTranslatorHelper as BaseAbstractHelper;

abstract class AbstractHelper extends BaseAbstractHelper
{
    /**
     * Get the ID of a grid
     *
     * If no ID attribute present, attempts to use the name attribute.
     * If no name attribute is present, either, returns null.
     *
     * @param  GridInterface $grid
     * @return null|string
     */
    public function getId(GridInterface $grid)
    {
        return $grid->getName();
    }
}
