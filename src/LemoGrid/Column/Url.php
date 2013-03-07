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
class Url extends AbstractColumn
{
	/**
	 * Column type
	 *
	 * @var string
	 */
	protected $_type = 'url';

	/**
	 * Column searchable state
	 *
	 * @var string
	 */
	protected $_isSearchable = false;

	/**
	 * Column sortable state
	 *
	 * @var string
	 */
	protected $_isSortable = false;

	/**
	 * @var string
	 */
	protected $_url = null;

	/**
	 * @var array
	 */
	protected $_attribs = array();

    /**
     * Render text value
     *
     * @see LemoBase\Grid.Column::render()
     */
    public function renderValue($value)
    {
		$url = urldecode($this->getUrl());
		$text = $this->getText();

		if($text == null) {
			$text = '&nbsp';
		}

		return '<a href="' . $url . '"' . $this->_htmlAttribs($this->getAttribs()) . '>' . $this->getGrid()->getView()->translate($text) . '</a>';
    }

	/**
	 * Nastavi hodnotu atributu href
	 *
	 * @param string $href
	 * @return LemoBase_JQuery_Grid_Column_Link
	 */
	public function setUrl($href)
	{
		$this->_url = $href;

		return $this;
	}

	/**
	 * Vrati hodnotu atributu href
	 *
	 * @return string
	 */
	public function getUrl()
	{
		if(null === $this->_url) {
			throw new Exception\InvalidArgumentException("Url for column '" . $this->getName() . "' is not specified!");
		}

		return $this->_url;
	}

	public function setAttribs(array $attribs)
	{
		$this->_attribs = $attribs;

		return $this;
	}

	public function getAttribs()
	{
		return $this->_attribs;
	}

	public function setText($text)
	{
		$this->_text = $text;

		return $this;
	}

	public function getText()
	{
		return $this->_text;
	}

	/**
	 * Converts an associative array to a string of tag attributes.
	 *
	 * @access public
	 *
	 * @param array $attribs From this array, each key-value pair is
	 * converted to an attribute name and value.
	 *
	 * @return string The XHTML for the attributes.
	 */
	protected function _htmlAttribs($attribs)
	{
		$xhtml   = '';
		foreach ((array) $attribs as $key => $val) {

			if (('on' == substr($key, 0, 2)) || ('constraints' == $key)) {
				// Don't escape event attributes; _do_ substitute double quotes with singles
				if (!is_scalar($val)) {
					// non-scalar data should be cast to JSON first
					$val = \Zend\Json\Json::encode($val);
				}
				// Escape single quotes inside event attribute values.
				// This will create html, where the attribute value has
				// single quotes around it, and escaped single quotes or
				// non-escaped double quotes inside of it
				$val = str_replace('\'', '&#39;', $val);
			} else {
				if (is_array($val)) {
					$val = implode(' ', $val);
				}
			}

			if (strpos($val, '"') !== false) {
				$xhtml .= " $key='$val'";
			} else {
				$xhtml .= " $key=\"$val\"";
			}

		}

		return $xhtml;
	}
}
