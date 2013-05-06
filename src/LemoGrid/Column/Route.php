<?php

namespace LemoGrid\Column;

use LemoGrid\Exception;
use Traversable;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;

class Route extends AbstractColumn
{
    /**
     * Column options
     *
     * @var RouteOptions
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
     * @param array|Traversable|RouteOptions       $options
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
     * @param  array|\Traversable|RouteOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Route
     */
    public function setOptions($options)
    {
        if (!$options instanceof RouteOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\Column\RouteOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new RouteOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return RouteOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new RouteOptions());
        }

        return $this->options;
    }

    public function renderValue()
    {
        if (null === $this->router) {
            throw new Exception\RuntimeException('No RouteStackInterface instance provided');
        }

        $name = $this->getOptions()->getRoute();
        $params = $this->getOptions()->getParams();
        $reuseMatchedParams = $this->getOptions()->getReuseMatchedParams();

        if (null === $name) {
            if (null === $this->routeMatch) {
                throw new Exception\RuntimeException('No RouteMatch instance provided');
            }

            $name = $this->routeMatch->getMatchedRouteName();

            if (null === $name) {
                throw new Exception\RuntimeException('RouteMatch does not contain a matched route name');
            }
        }

        if ($reuseMatchedParams && $this->routeMatch !== null) {
            $routeMatchParams = $this->routeMatch->getParams();

            if (isset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
                $routeMatchParams['controller'] = $routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
                unset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
            }

            if (isset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE])) {
                unset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE]);
            }

            $params = array_merge($routeMatchParams, $params);
        }

        $options['name'] = $name;

        $link = $this->router->assemble($params, $options);

        return urldecode(sprintf(
            $this->getOptions()->getTemplate(),
            $link,
            $this->getOptions()->getText()
        ));
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
