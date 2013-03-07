<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Column\Exception;

/**
 * @uses       LemoBase\Grid\Column\Exception
 * @uses       \BadMethodCallException
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Column
 */
class BadMethodCallException
    extends \BadMethodCallException
    implements \LemoBase\Grid\Column\Exception
{}
