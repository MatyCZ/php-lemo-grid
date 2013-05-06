<?php

namespace LemoGrid;

interface GridFactoryAwareInterface
{
    /**
     * Compose a grid factory into the object
     *
     * @param Factory $factory
     */
    public function setGridFactory(Factory $factory);

    /**
     * Retrive grid factory
     *
     * @return Factory
     */
    public function getGridFactory();
}
