<?php

namespace LemoGrid;

interface GridFactoryAwareInterface
{
    /**
     * Compose a grid factory into the object
     
     * 
*@param GridFactory $factory
     */
    public function setGridFactory(GridFactory $factory);

    /**
     * Retrive grid factory
     
     * 
*@return GridFactory
     */
    public function getGridFactory();
}
