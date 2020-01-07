<?php

namespace LemoGrid\Column;

use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\Exception;
use Traversable;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Router\RouteMatch;
use Laminas\Router\RouteStackInterface;

class Link extends AbstractColumn
{
    /**
     * Column options
     *
     * @var LinkOptions
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
     * @param  array|\Traversable|LinkOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Route
     */
    public function setOptions($options)
    {
        if (!$options instanceof LinkOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\Column\LinkOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new LinkOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return LinkOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new LinkOptions());
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

        $link = urldecode($this->router->assemble($params, $options));

        $text = $this->getOptions()->getText();
        if (empty($text)) {
            $text = $this->getValue();
        }

        return sprintf(
            $this->getOptions()->getTemplate(),
            $link,
            $text
        );
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
