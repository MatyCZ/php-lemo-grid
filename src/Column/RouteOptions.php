<?php

namespace Lemo\Grid\Column;

use Laminas\Stdlib\AbstractOptions;

class RouteOptions extends AbstractOptions
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var bool
     */
    protected $reuseMatchedParams = false;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $template = '<a href="%s">%s</a>';

    /**
     * @param array $options
     * @return RouteOptions
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $params
     * @return RouteOptions
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param boolean $reuseMatchedParams
     * @return RouteOptions
     */
    public function setReuseMatchedParams($reuseMatchedParams)
    {
        $this->reuseMatchedParams = $reuseMatchedParams;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getReuseMatchedParams()
    {
        return $this->reuseMatchedParams;
    }

    /**
     * @param  string $route
     * @return RouteOptions
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param  string $text
     * @return RouteOptions
     */
    public function setText($text)
    {
        $this->text = (string) $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param  string $template
     * @return RouteOptions
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
