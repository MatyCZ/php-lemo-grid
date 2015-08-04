<?php

namespace LemoGrid\Platform;

use LemoGrid\Exception;
use LemoGrid\Renderer\JqGridRenderer;
use LemoGrid\ResultSet\JqGrid as JqGridResultSet;
use LemoGrid\ResultSet\ResultSetInterface;
use Traversable;
use Zend\Json;

class JqGrid extends AbstractPlatform
{
    /**
     * @var array
     */
    protected $buttons;

    /**
     * @var JqGridOptions
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
     * @param  string      $name
     * @param  string|null $label
     * @param  string|null $icon
     * @param  string      $callback
     * @return JqGrid
     */
    public function addButton($name, $label = null, $icon = null, $callback)
    {
        $this->buttons[$name] = array(
            'name' => $name,
            'label' => $label,
            'icon' => $icon,
            'callback' => $callback,
        );

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
     * Set grid options
     *
     * @param  array|Traversable|JqGridOptions $options
     * @throws Exception\InvalidArgumentException
     * @return JqGrid
     */
    public function setOptions($options)
    {
        if (!$options instanceof JqGridOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\Platform\JqGridOptions; '
                        . 'received "%s"', get_class($options))
                );
            }

            $options = new JqGridOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get grid options
     *
     * @return JqGridOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new JqGridOptions());
        }

        return $this->options;
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
     * @return mixed|bool
     */
    public function modifyParam($key, $value)
    {
        // Modify params
        if ('filters' == $key) {
            if (is_array($value)) {
                $rules = $value;
            } else {
                $rules = Json\Decoder::decode(stripslashes($value), Json\Json::TYPE_ARRAY);
            }

            $value = array();
            $value['operator'] = strtolower($rules['groupOp']);
            foreach ($rules['rules'] as $rule) {
                $value['rules'][$rule['field']][] = array(
                    'operator' => $this->getFilterOperator($rule['op']),
                    'value' => addcslashes(trim($rule['data']), "'_%\\\""),
                );
            }
        }

        // Dont save grid name to Session
        if ('_name' == $key) {
            $this->isRendered = true;
            return false;
        }

        return $value;
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

            if (!empty($param)) {
                $number = $param;
            }
        }

        return $number;
    }

    /**
     * Return sort by column name => direct
     *
     * @return array
     * @throws Exception\UnexpectedValueException
     */
    public function getSort()
    {
        $sort = array();

        // Nacteme vychozi razeni
        $column = $this->getOptions()->getSortName();
        $direct = $this->getOptions()->getSortOrder();

        // Nacteme razeni z parametru z Requestu
        if ($this->getGrid()->hasParam('sidx')) {
            $sidx = strtolower($this->getGrid()->getParam('sidx'));
            if (!empty($sidx)) {
                $column = $sidx;
            }
        }
        if ($this->getGrid()->hasParam('sord')) {
            $sord = strtolower($this->getGrid()->getParam('sord'));
            if (!empty($sord)) {
                if($sord != 'asc' && $sord != 'desc') {
                    throw new Exception\UnexpectedValueException('Sort direct must be ' . 'asc' . ' or ' . 'desc' . '!');
                }

                $direct = $sord;
            }
        }

        // Osetrime vstup
        $column = trim($column);
        $direct = trim($direct);

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
     * Get class of platform renderer
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
     * @param  null|JqGridResultSet $resultSet
     * @return JqGrid
     */
    public function setResultSet($resultSet)
    {
        if (null !== $resultSet && !$resultSet instanceof ResultSetInterface) {
            throw new Exception\InvalidResultSetException('ResultSet must be instance of JqGridResultSet');
        }

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
