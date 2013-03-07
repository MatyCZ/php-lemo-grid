<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Column\Exception;

/**
 * @uses       LemoBase\Grid\Column\Exception
 * @uses       \UnexcpectedValueException
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Column
 */
class UnexpectedValueException
    extends \UnexpectedValueException
    implements \LemoBase\Grid\Column\Exception
{}
