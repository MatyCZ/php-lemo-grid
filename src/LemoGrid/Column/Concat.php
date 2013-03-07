<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Column;

use LemoBase\Grid\Column\AbstractColumn;

/**
 * LemoBase_Grid_Column_Concat
 *
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Column
 */
class Concat extends AbstractColumn
{
    /**
     * Column type
     *
     * @var string
     */
    protected $_type = 'concat';

    /**
     * @var array
     */
    protected $_identifiers = array();

    /**
     * Separator between pieces
     *
     * @var string
     */
    protected $_separator = ' ';

    /**
     * Render text value
     *
     * @see LemoBase\Grid\Column::render()
     */
    public function renderValue($value)
    {
        return $value;
    }

    /**
     * @param array $identifiers
     * @return Concat
     */
    public function setIdentifiers(array $identifiers)
    {
        $this->_identifiers = $identifiers;

        return $this;
    }

    /**
     * @return array
     */
    public function getIdentifiers()
    {
        return $this->_identifiers;
    }

    /**
     * @param string $separator
     * @return Concat
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }
}
