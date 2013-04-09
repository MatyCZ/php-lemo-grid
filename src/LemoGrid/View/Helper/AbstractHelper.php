<?php

namespace LemoGrid\View\Helper;

use LemoGrid\Exception;
use LemoGrid\GridInterface;
use Zend\I18n\View\Helper\AbstractTranslatorHelper as BaseAbstractHelper;

abstract class AbstractHelper extends BaseAbstractHelper
{
    /**
     * @var GridInterface
     */
    protected $grid;

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

    /**
     * Set instance of Grid
     *
     * @param  GridInterface $grid
     * @return JqGrid
     */
    public function setGrid(GridInterface $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Retrieve instance of Grid
     *
     * @throws Exception\UnexpectedValueException
     * @return GridInterface
     */
    public function getGrid()
    {
        return $this->grid;
    }
}
