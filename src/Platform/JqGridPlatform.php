<?php

namespace Lemo\Grid\Platform;

use Lemo\Grid\Exception;
use Lemo\Grid\GridInterface;
use Lemo\Grid\Renderer\JqGridRenderer;
use Lemo\Grid\Renderer\RendererInterface;
use Lemo\Grid\ResultSet\JqGridResultSet;
use Lemo\Grid\ResultSet\ResultSetInterface;
use Traversable;
use Laminas\Json;

class JqGridPlatform extends AbstractPlatform
{
    /**
     * @var array
     */
    protected $buttons;

    /**
     * @var JqGridPlatformOptions
     */
    protected $options;

    /**
     * Is grid rendered?
     *
     * @var bool
     */
    protected $isRendered = false;

    /**
     * @var JqGridRenderer
     */
    protected $renderer;

    /**
     * @var JqGridResultSet
     */
    protected $resultSet;

    /**
     * Set grid options
     *
     * @param  array|Traversable|JqGridPlatformOptions $options
     * @throws Exception\InvalidArgumentException
     * @return JqGridPlatform
     */
    public function setOptions($options)
    {
        if (!$options instanceof JqGridPlatformOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                        'Expected instance of Lemo\Grid\Platform\JqGridOptions; '
                        . 'received "%s"', get_class($options))
                );
            }

            $options = new JqGridPlatformOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get grid options
     *
     * @return JqGridPlatformOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new JqGridPlatformOptions());
        }

        return $this->options;
    }

    /**
     * @param  string      $name
     * @param  string|null $label
     * @param  string|null $icon
     * @param  string      $callback
     * @return JqGridPlatform
     */
    public function addButton($name, $label = null, $icon = null, $callback)
    {
        $this->buttons[$name] = [
            'name' => $name,
            'label' => $label,
            'icon' => $icon,
            'callback' => $callback,
        ];

        return $this;
    }

    /**
     * @param  string $name
     * @return array|null
     */
    public function getButton($name)
    {
        if (isset($this->buttons[$name])) {
            return $this->buttons[$name];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * Is the grid rendered?
     *
     * @return bool
     */
    public function isRendered()
    {
        return $this->isRendered;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function modifyParam($key, $value)
    {
        // Modify params
        if ('filters' == $key) {
            if (is_array($value)) {
                $rules = $value;
            } else {
                $rules = Json\Decoder::decode($value, Json\Json::TYPE_ARRAY);
            }

            if (empty($rules['groupOp'])) {
                return $value;
            }

            $value = [];
            $value['operator'] = strtolower($rules['groupOp']);
            foreach ($rules['rules'] as $rule) {
                $value['rules'][$rule['field']][] = [
                    'operator' => $this->getFilterOperator($rule['op']),
                    'value' => trim($rule['data']),
                ];
            }
        }

        if ('rows' === $key) {
            $options = $this->getOptions();
            if (null !== $value && !in_array($value, $options->getRecordsPerPageList())) {
                $value = $options->getRecordsPerPage();
            }
        }

        // Dont save grid name to Session
        if ('_name' == $key) {
            $this->isRendered = true;
        }

        return $value;
    }

    /**
     * @param  GridInterface $grid
     * @param  Traversable   $params
     * @return bool
     */
    public function canUseParams(GridInterface $grid, Traversable $params)
    {
        if ($params->offsetExists('_name') && $params->offsetGet('_name') == $grid->getName()) {
            return true;
        }

        return false;
    }

    /**
     * Return converted filter operator
     *
     * @param  string $operator
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getFilterOperator($operator)
    {
        switch ($operator) {
            case 'eq':
                $operator = self::OPERATOR_EQUAL;
                break;
            case 'ne':
                $operator = self::OPERATOR_NOT_EQUAL;
                break;
            case 'lt':
                $operator = self::OPERATOR_LESS;
                break;
            case 'le':
                $operator = self::OPERATOR_LESS_OR_EQUAL;
                break;
            case 'gt':
                $operator = self::OPERATOR_GREATER;
                break;
            case 'ge':
                $operator = self::OPERATOR_GREATER_OR_EQUAL;
                break;
            case 'bw':
                $operator = self::OPERATOR_BEGINS_WITH;
                break;
            case 'bn':
                $operator = self::OPERATOR_NOT_BEGINS_WITH;
                break;
            case 'in':
                $operator = self::OPERATOR_IN;
                break;
            case 'ni':
                $operator = self::OPERATOR_NOT_IN;
                break;
            case 'ew':
                $operator = self::OPERATOR_ENDS_WITH;
                break;
            case 'en':
                $operator = self::OPERATOR_NOT_ENDS_WITH;
                break;
            case 'cn':
                $operator = self::OPERATOR_CONTAINS;
                break;
            case 'nc':
                $operator = self::OPERATOR_NOT_CONTAINS;
                break;
            default:
                throw new Exception\InvalidArgumentException('Invalid filter operator');
        }

        return $operator;
    }

    /**
     * Return converted filter operator
     *
     * @param  string $operator
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getFilterOperatorOutput($operator)
    {
        switch ($operator) {
            case self::OPERATOR_EQUAL:
                $operator = 'eq';
                break;
            case self::OPERATOR_NOT_EQUAL:
                $operator = 'ne';
                break;
            case self::OPERATOR_LESS:
                $operator = 'lt';
                break;
            case self::OPERATOR_LESS_OR_EQUAL:
                $operator = 'le';
                break;
            case self::OPERATOR_GREATER:
                $operator = 'gt';
                break;
            case self::OPERATOR_GREATER_OR_EQUAL:
                $operator = 'ge';
                break;
            case self::OPERATOR_BEGINS_WITH:
                $operator = 'bw';
                break;
            case self::OPERATOR_NOT_BEGINS_WITH:
                $operator = 'bn';
                break;
            case self::OPERATOR_IN:
                $operator = 'in';
                break;
            case self::OPERATOR_NOT_IN:
                $operator = 'ni';
                break;
            case self::OPERATOR_ENDS_WITH:
                $operator = 'ew';
                break;
            case self::OPERATOR_NOT_ENDS_WITH:
                $operator = 'en';
                break;
            case self::OPERATOR_CONTAINS:
                $operator = 'cn';
                break;
            case self::OPERATOR_NOT_CONTAINS:
                $operator = 'nc';
                break;
            default:
                throw new Exception\InvalidArgumentException('Invalid filter operator');
        }

        return $operator;
    }

    /**
     * Get number of current page
     *
     * @return int
     */
    public function getNumberOfCurrentPage()
    {
        $page = $this->getOptions()->getPage();

        if ($this->getGrid()->hasParam('page')) {
            $param = $this->getGrid()->getParam('page');
            if (!empty($param)) {
                $page = $param;
            }
        }

        return $page;
    }

    /**
     * Get number of visible rows
     *
     * @return int
     */
    public function getNumberOfVisibleRows()
    {
        $number = $this->getOptions()->getRecordsPerPage();

        if ($this->getGrid()->hasParam('rows')) {
            $param = $this->getGrid()->getParam('rows');

            if (
                !empty($param)
                && in_array($param, $this->getOptions()->getRecordsPerPageList())
            ) {
                $number = $param;
            }
        }

        return $number;
    }

    /**
     * Return sort by column name => direct
     *
     * @return array
     */
    public function getSort()
    {
        $sort = [];

        // Nacteme vychozi razeni
        $column = $this->getOptions()->getSortName();
        $direct = $this->getOptions()->getSortOrder();

        // Nacteme razeni z parametru z Requestu
        if ($this->getGrid()->hasParam('sidx')) {
            $sidx = $this->getGrid()->getParam('sidx');
            if (!empty($sidx)) {
                $column = $sidx;
            }
        }
        if ($this->getGrid()->hasParam('sord')) {
            $sord = $this->getGrid()->getParam('sord');
            if (!empty($sord)) {
                $direct = $sord;
            }
        }

        // Osetrime vstup
        $column = trim($column);
        $direct = trim($direct);

        if (
            false === $this->getGrid()->has($column)
            || !in_array(strtolower($direct), ['asc', 'desc'])
        ) {
            return $sort;
        }

        // Sestavime shodne retezce ve formatu (sloupec smer)
        if (strpos($column, ', ')) {
            $parts = explode(', ', $column);
            $partsCount = count($parts);

            // Doplnime
            $parts[$partsCount-1] .= ' ' . $direct;
        } else {
            if (!empty($column)) {
                $parts[] = $column . ' ' . $direct;
            }
        }

        // Z jednotlivych casti sestavime pole ve formatu (sloupec => smer)
        if (!empty($parts)) {
            foreach ($parts as $part) {
                $subParts = explode(' ', $part);

                $sort[$subParts[0]] = $subParts[1];
            }
        }

        return $sort;
    }

    /**
     * Set instance of platform renderer
     *
     * @param  RendererInterface $renderer
     * @return JqGridPlatform
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Get instance of platform renderer
     *
     * @return JqGridRenderer
     */
    public function getRenderer()
    {
        if (null === $this->renderer) {
            $this->renderer = new JqGridRenderer();
        }

        return $this->renderer;
    }

    /**
     * Set platform resultset
     *
     * @param  ResultSetInterface $resultSet
     * @return JqGridPlatform
     */
    public function setResultSet(ResultSetInterface $resultSet)
    {
        $this->resultSet = $resultSet;

        return $this;
    }

    /**
     * Get class of platform resultset
     *
     * @return JqGridResultSet
     */
    public function getResultSet()
    {
        if (null === $this->resultSet) {
            $this->resultSet = new JqGridResultSet();
        }

        return $this->resultSet;
    }
}
