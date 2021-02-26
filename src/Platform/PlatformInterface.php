<?php

namespace Lemo\Grid\Platform;

use Lemo\Grid\Exception;
use Lemo\Grid\GridInterface;
use Lemo\Grid\Renderer\RendererInterface;
use Lemo\Grid\ResultSet\ResultSetInterface;
use Traversable;
use Laminas\Stdlib\AbstractOptions;

interface PlatformInterface
{
    /**
     * Set grid instance
     *
     * @param  GridInterface $grid
     * @return PlatformInterface
     */
    public function setGrid(GridInterface $grid);

    /**
     * Get grid instance
     *
     * @return GridInterface
     */
    public function getGrid();

    /**
     * Set options for a column
     *
     * @param  array|Traversable|AbstractOptions $options
     * @return PlatformInterface
     */
    public function setOptions($options);

    /**
     * Retrieve options for a column
     *
     * @return AbstractOptions
     */
    public function getOptions();

    /**
     * Is the grid rendered?
     *
     * @return bool
     */
    public function isRendered();

    /**
     * Can grid use params from current request?
     *
     * @param  GridInterface $grid
     * @param  Traversable   $params
     * @return bool
     */
    public function canUseParams(GridInterface $grid, Traversable $params);

    /**
     * Modify param
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function modifyParam(string $key, $value);

    /**
     * Return converted filter operator
     *
     * @param  string $operator
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getFilterOperator($operator);

    /**
     * Return converted filter operator
     *
     * @param  string $operator
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getFilterOperatorOutput($operator);

    /**
     * Get number of current page
     *
     * @return int
     */
    public function getNumberOfCurrentPage();

    /**
     * Get number of visible rows
     *
     * @return int
     */
    public function getNumberOfVisibleRows();

    /**
     * Return sort by column name => direct
     *
     * @return array
     */
    public function getSort();

    /**
     * Set instance of platform renderer

     *
*@param  RendererInterface $renderer
     * @return JqGridPlatform
     */
    public function setRenderer(RendererInterface $renderer);

    /**
     * Get instance of platform renderer
     *
     * @return RendererInterface
     */
    public function getRenderer();

    /**
     * Set instance of platform resultset

     *
*@param  ResultSetInterface $resultSet
     * @return JqGridPlatform
     */
    public function setResultSet(ResultSetInterface $resultSet);

    /**
     * Get instance of platform resultset
     *
     * @return ResultSetInterface
     */
    public function getResultSet();
}
