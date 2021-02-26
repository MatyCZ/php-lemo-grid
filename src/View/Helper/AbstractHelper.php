<?php

namespace Lemo\Grid\View\Helper;

use Lemo\Grid\Exception;
use Lemo\Grid\GridInterface;
use Laminas\I18n\View\Helper\AbstractTranslatorHelper as BaseAbstractHelper;

abstract class AbstractHelper extends BaseAbstractHelper
{
    /**
     * @var GridInterface|null
     */
    protected ?GridInterface $grid = null;

    /**
     * Get the ID of a grid
     *
     * If no ID attribute present, attempts to use the name attribute.
     * If no name attribute is present, either, returns null.
     *
     * @param  GridInterface $grid
     * @return string|null
     */
    public function getId(GridInterface $grid): ?string
    {
        return $grid->getName();
    }

    /**
     * Set instance of Grid
     *
     * @param  GridInterface $grid
     * @return self
     */
    public function setGrid(GridInterface $grid): self
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
    public function getGrid(): GridInterface
    {
        return $this->grid;
    }
}
