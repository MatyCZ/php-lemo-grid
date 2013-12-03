<?php

namespace LemoGrid\Column;

use LemoGrid\Exception;
use Traversable;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;

class Buttons extends AbstractColumn
{
    /**
     * Column options
     *
     * @var ButtonsOptions
     */
    protected $options;

    /**
     * RouteStackInterface instance.
     *
     * @var RouteStackInterface
     */
    protected $router;

    /**
     * RouteInterface match returned by the router.
     *
     * @var RouteMatch.
     */
    protected $routeMatch;

    /**
     * @param null|string                        $name
     * @param array|Traversable|ButtonsOptions   $options
     * @param array|Traversable|ColumnAttributes $attributes
     */
    public function __construct($name = null, $options = null, $attributes = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $options) {
            $this->setOptions($options);
        }

        if (null !== $attributes) {
            $this->setAttributes($attributes);
        }
    }

    /**
     * Set column options
     *
     * @param  array|\Traversable|ButtonsOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Buttons
     */
    public function setOptions($options)
    {
        if (!$options instanceof ButtonsOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                        'Expected instance of LemoGrid\Column\ButtonsOptions; '
                        . 'received "%s"', get_class($options))
                );
            }

            $options = new ButtonsOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return ButtonsOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new ButtonsOptions());
        }

        return $this->options;
    }

    /**
     * @return string
     */
    public function renderValue()
    {
        $parts = array();
        foreach ($this->getOptions()->getButtons() as $button) {
            if ($button instanceof Route) {
                $button->setRouter($this->router);
                $button->setRouteMatch($this->routeMatch);
            }

            $parts[] = $button->renderValue();
        }

        return implode($this->getOptions()->getSeparator(), $parts);
    }

    /**
     * Set the router to use for assembling.
     *
     * @param RouteStackInterface $router
     * @return Route
     */
    public function setRouter(RouteStackInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Set route match returned by the router.
     *
     * @param  RouteMatch $routeMatch
     * @return self
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;
        return $this;
    }
}
