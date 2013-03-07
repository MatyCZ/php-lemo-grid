<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Column;

/**
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Column
 */
interface ColumnInterface
{
	/**
	 * Render column value
	 *
	 * @param string
	 * @return string
	 * @throws Exception
	 */
	public function renderValue($value);
}
