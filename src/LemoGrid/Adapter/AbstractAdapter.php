<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Adapter;

use LemoBase\Grid\Grid,
	Zend\Config\Config,
	Zend\Stdlib\IteratorToArray,
	Traversable;

/**
 * LemoBase_Grid_Adapter_AbstractAdapter
 *
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Adapter
 */
abstract class AbstractAdapter
{
	/**
	 * @var \LemoBase\Grid\Grid
	 */
	protected $_grid;

	/**
	 * Default sorting column index
	 *
	 * @var string|array
	 */
	protected $_defaultSortColumn = null;

	/**
	 * Default sorting direct
	 *
	 * @var string|array
	 */
	protected $_defaultSortDirection = null;

	/**
	 * Number of data source records
	 *
	 * @var int
	 */
	protected $_records = 0;

	/**
	 * Number of filtered data source records
	 *
	 * @var int
	 */
	protected $_recordsFiltered = 0;

	/**
	 * Get sort column name
	 *
	 * @var string
	 */
	protected $_sortColumn = null;

	/**
	 * Get sort column direct
	 *
	 * @var string
	 */
	protected $_sortDirect = null;

	/**
	 * Column type
	 *
	 * @var string
	 */
	protected $_type;

	/**
	 * Constructor
	 *
	 * @param  array|Traversable $options
	 * @return void
	 */
	public function __construct($options = null)
	{
		if ($options instanceof Traversable) {
			$options = IteratorToArray::convert($options);
		}
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}

	/**
	 * Set object state from options array
	 *
	 * @param  array $options
	 * @return AbstractAdapter
	 */
	public function setOptions(array $options)
	{
		unset($options['options']);
		unset($options['config']);

		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);

			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}

		return $this;
	}

	/**
	 * Set object state from Zend_Config object
	 *
	 * @param  Config $config
	 * @return AbstractAdapter
	 */
	public function setConfig(Config $config)
	{
		return $this->setOptions($config->toArray());
	}

	// Metadata

	/**
	 * @param \LemoBase\Grid\Grid $grid
	 * @return AbstractAdapter
	 */
	public function setGrid(Grid $grid)
	{
		$this->_grid = $grid;

		return $this;
	}

	/**
	 * @return \LemoBase\Grid\Grid
	 */
	public function getGrid()
	{
		return $this->_grid;
	}

	/**
	 * Set default sort direction
	 *
	 * @param array|string $defaultSortDirection
	 * @return Grid
	 */
	public function setDefaultSortDirection($defaultSortDirect)
	{
		$this->_defaultSortDirection = $defaultSortDirect;

		return $this;
	}

	/**
	 * Get default sort direction
	 *
	 * @return array|string
	 */
	public function getDefaultSortDirection()
	{
		return $this->_defaultSortDirection;
	}

	/**
	 * Set default sort column
	 *
	 * @param array|string $defaultSortColumn
	 * @return Grid
	 */
	public function setDefaultSortColumn($defaultSortColumn)
	{
		$this->_defaultSortColumn = $defaultSortColumn;

		return $this;
	}

	/**
	 * Return default sort column
	 *
	 * @return array|string
	 */
	public function getDefaultSortColumn()
	{
		return $this->_defaultSortColumn;
	}

	/**
	 * Return sort by column index
	 *
	 * @return string
	 */
	public function getSortColumn()
	{
		if(null === $this->_sortColumn) {
			$queryParams = $this->getGrid()->getQueryParams();

			if(isset($queryParams['sidx'])) {
				$this->_sortColumn = $queryParams['sidx'];
			} else {
				$this->_sortColumn = $this->getGrid()->getDefaultSortColumn();
			}
		}

		return $this->_sortColumn;
	}

	/**
	 * Return sort direct
	 *
	 * @return string
	 */
	public function getSortDirect()
	{
		if(null === $this->_sortDirect) {
			$queryParams = $this->getGrid()->getQueryParams();
			if(isset($queryParams['sidx'])) {
				if(isset($queryParams['sord'])) {
					if(strtolower($queryParams['sord']) != 'asc' AND strtolower($queryParams['sord']) != 'desc') {
						throw new Exception\UnexpectedValueException('Sort direct must be ' . 'asc' . ' or ' . 'desc' . '!');
					}

					$this->_sortDirect = $queryParams['sord'];
				} else {
					$this->_sortDirect = 'asc';
				}
			} else {
				$this->_sortDirect = $this->getGrid()->getDefaultSortOrder();
			}
		}

		return $this->_sortDirect;
	}

	/**
	 * Return adapter type
	 *
	 * @return string
	 */
	public function getType()
	{
		if (null === $this->_type) {
			$this->_type = get_class($this);
		}

		return $this->_type;
	}

	// Neco

	/**
	 * Get number of current page
	 *
	 * @return int
	 */
	public function getNumberOfPages()
	{
	    return ceil($this->getNumberOfRecords() / $this->getGrid()->getRecordsPerPage());
	}

	/**
	 * Get number of current page
	 *
	 * @return int
	 */
	public function getNumberOfCurrentPage()
	{
	    $page = $this->getGrid()->getQueryParam('page');

	    if(null === $page) {
	        $page = $this->getGrid()->getDefaultPage();
	    }

	    return $page;
	}

	/**
	 * Return number of data source records
	 *
	 * @return int
	 */
	public function getNumberOfRecords()
	{
		return $this->_records;
	}

	/**
	 * Return number of filtered data source records
	 *
	 * @return int
	 */
	public function getNumberOfFilteredRecords()
	{
		return $this->_recordsFiltered;
	}
}
