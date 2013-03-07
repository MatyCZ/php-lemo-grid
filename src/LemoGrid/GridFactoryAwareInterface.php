<?php

namespace LemoGrid;

interface GridFactoryAwareInterface
{
    /**
     * Compose a form factory into the object
     *
     * @param Factory $factory
     */
    public function setGridFactory(Factory $factory);
}
