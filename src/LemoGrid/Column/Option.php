<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Column;

use LemoBase\Grid\Column\AbstractColumn;

/**
 * LemoBase_Grid_Column_Option
 *
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Column
 */
class Option extends AbstractColumn
{
	/**
	 * Column type
	 *
	 * @var string
	 */
	protected $_type = 'option';

	/**
	 * Array of options for multi-item
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Which values are translated already?
	 *
	 * @var array
	 */
	protected $_translated = array();

	/**
	 * Render text value
	 *
	 * @see LemoBase\Grid.Column::render()
	 */
	public function renderValue($value)
	{
		$options = $this->getMultiOptions();

		if(isset($options[$value])) {
			return $this->getGrid()->getView()->translate($options[$value]);
		} else {
			return $value;
		}
	}

	/**
	 * Add an option
	 *
	 * @param  string $option
	 * @param  string $value
	 * @return \LemoBase\Grid\Column\Option
	 */
	public function addMultiOption($option, $value = '')
	{
		$option  = (string) $option;

		if (!$this->_translateOption($option, $value)) {
			$this->options[$option] = $this->_translateValue($value);
		}

		return $this;
	}

	/**
	 * Add many options at once
	 *
	 * @param  array $options
	 * @return \LemoBase\Grid\Column\Option
	 */
	public function addMultiOptions(array $options)
	{
		foreach ($options as $option => $value) {
			if (is_array($value)
				&& array_key_exists('key', $value)
				&& array_key_exists('value', $value)
			) {
				$this->addMultiOption($value['key'], $value['value']);
			} else {
				$this->addMultiOption($option, $value);
			}
		}
		return $this;
	}

	/**
	 * Set all options at once (overwrites)
	 *
	 * @param  array $options
	 * @return \LemoBase\Grid\Column\Option
	 */
	public function setMultiOptions(array $options)
	{
		$this->clearMultiOptions();
		return $this->addMultiOptions($options);
	}

	/**
	 * Retrieve single multi option
	 *
	 * @param  string $option
	 * @return mixed
	 */
	public function getMultiOption($option)
	{
		$option  = (string) $option;
		if (isset($this->options[$option])) {
			$this->_translateOption($option, $this->options[$option]);
			return $this->options[$option];
		}

		return null;
	}

	/**
	 * Retrieve options
	 *
	 * @return array
	 */
	public function getMultiOptions()
	{
		foreach ($this->options as $option => $value) {
			$this->_translateOption($option, $value);
		}
		return $this->options;
	}

	/**
	 * Remove a single multi option
	 *
	 * @param  string $option
	 * @return bool
	 */
	public function removeMultiOption($option)
	{
		$option  = (string) $option;

		if (isset($this->options[$option])) {
			unset($this->options[$option]);
			if (isset($this->_translated[$option])) {
				unset($this->_translated[$option]);
			}
			return true;
		}

		return false;
	}

	/**
	 * Clear all options
	 *
	 * @return \LemoBase\Grid\Column\Option
	 */
	public function clearMultiOptions()
	{
		$this->options = array();
		$this->_translated = array();
		return $this;
	}

	/**
	 * Translate an option
	 *
	 * @param  string $option
	 * @param  string $value
	 * @return bool
	 */
	protected function _translateOption($option, $value)
	{
		if (!isset($this->_translated[$option]) && !empty($value)) {
			$this->options[$option] = $this->_translateValue($value);
			if ($this->options[$option] === $value) {
				return false;
			}
			$this->_translated[$option] = true;
			return true;
		}

		return false;
	}

	/**
	 * Translate a multi option value
	 *
	 * @param  string $value
	 * @return string
	 */
	protected function _translateValue($value)
	{
		if (is_array($value)) {
			foreach ($value as $key => $val) {
				$value[$key] = $this->_translateValue($val);
			}
			return $value;
		} else {
			return $value;
		}
	}
}
