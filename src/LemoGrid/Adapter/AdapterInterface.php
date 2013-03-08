<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Adapter;

/**
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Adapter
 */
interface AdapterInterface
{
    /**
     * Get data from adapter
     *
     * @return array
     */
    public function getData();
}
