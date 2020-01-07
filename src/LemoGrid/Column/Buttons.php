<?php

namespace LemoGrid\Column;

use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\Exception;
use Traversable;
use Laminas\Router\RouteInterface;
use Laminas\Router\RouteMatch;
use Laminas\Router\RouteStackInterface;

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
     * @param  AdapterInterface $adapter
     * @param  array            $item
     * @return string
     */
    public function renderValue(AdapterInterface $adapter, array $item)
    {
        $parts = [];
        foreach ($this->getOptions()->getButtons() as $button) {
            if ($button->isValid($adapter, $item)) {
                if ($button instanceof Route && $this->router instanceof RouteInterface) {
                    $button->setRouter($this->router);
                    $button->setRouteMatch($this->routeMatch);
                }

                $parts[] = $button->renderValue($adapter, $item);
            }
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
     * @return RouteStackInterface
     */
    public function getRouter()
    {
        return $this->router;
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

    /**
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }
}
