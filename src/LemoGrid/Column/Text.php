<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Column;

use LemoBase\Grid\Column\AbstractColumn;

/**
 * LemoBase_Grid_Column_Text
 *
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Column
 */
class Text extends AbstractColumn
{
	/**
	 * Column type
	 *
	 * @var string
	 */
	protected $_type = 'text';

    /**
     * Render text value
     *
     * @see LemoBase\Grid.Column::render()
     */
    public function renderValue($value)
    {
		return $this->getGrid()->getView()->translate($value);
    }
}
