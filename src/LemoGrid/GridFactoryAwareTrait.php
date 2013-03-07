<?php

namespace LemoGrid;

use \LemoGrid\Factory;

trait GridFactoryAwareTrait
{
    /**
     * @var Factory
     */
    protected $factory = null;

    /**
     * Compose a grid factory into the object
     *
     * @param Factory $factory
     * @return mixed
     */
    public function setGridFactory(Factory $factory)
    {
        $this->factory = $factory;

        return $this;
    }
}
