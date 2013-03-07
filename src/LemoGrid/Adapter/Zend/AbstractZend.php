<?php

namespace LemoBase\Grid\Adapter\Zend;

use LemoBase\Grid\Adapter\AbstractAdapter;

class AbstractZend extends AbstractAdapter
{
	/**
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;

	/**
	 * Set adapter
	 *
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @return AbstractZend;
	 */
	public function setAdapter($adapter)
	{
		$this->adapter = $adapter;
		return $this;
	}

	/**
	 * Get adapter
	 *
	 * @return \Zend\Db\Adapter\Adapter
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}
}
