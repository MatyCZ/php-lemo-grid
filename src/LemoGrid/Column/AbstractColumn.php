<?php

namespace LemoGrid\Column;

use LemoGrid\Adapter\AbstractAdapter;
use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\GridInterface;
use LemoGrid\Exception;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\InitializableInterface;

abstract class AbstractColumn implements
    ColumnInterface,
    InitializableInterface,
    ColumnPrepareAwareInterface
{
    /**
     * @var ColumnAttributes
     */
    protected $attributes;

    /**
     * Standard boolean attributes, with expected values for enabling/disabling
     *
     * @var array
     */
    protected $booleanAttributes = array(
        'autocomplete' => array('on' => 'on',        'off' => 'off'),
        'autofocus'    => array('on' => 'autofocus', 'off' => ''),
        'checked'      => array('on' => 'checked',   'off' => ''),
        'disabled'     => array('on' => 'disabled',  'off' => ''),
        'multiple'     => array('on' => 'multiple',  'off' => ''),
        'readonly'     => array('on' => 'readonly',  'off' => ''),
        'required'     => array('on' => 'required',  'off' => ''),
        'selected'     => array('on' => 'selected',  'off' => ''),
    );

    /**
     * @var ColumnCondition[]
     */
    protected $conditions = array();

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Attributes globally valid for all tags
     *
     * @var array
     */
    protected $validGlobalAttributes = array(
        'accesskey'          => true,
        'class'              => true,
        'contenteditable'    => true,
        'contextmenu'        => true,
        'dir'                => true,
        'draggable'          => true,
        'dropzone'           => true,
        'hidden'             => true,
        'id'                 => true,
        'lang'               => true,
        'onabort'            => true,
        'onblur'             => true,
        'oncanplay'          => true,
        'oncanplaythrough'   => true,
        'onchange'           => true,
        'onclick'            => true,
        'oncontextmenu'      => true,
        'ondblclick'         => true,
        'ondrag'             => true,
        'ondragend'          => true,
        'ondragenter'        => true,
        'ondragleave'        => true,
        'ondragover'         => true,
        'ondragstart'        => true,
        'ondrop'             => true,
        'ondurationchange'   => true,
        'onemptied'          => true,
        'onended'            => true,
        'onerror'            => true,
        'onfocus'            => true,
        'oninput'            => true,
        'oninvalid'          => true,
        'onkeydown'          => true,
        'onkeypress'         => true,
        'onkeyup'            => true,
        'onload'             => true,
        'onloadeddata'       => true,
        'onloadedmetadata'   => true,
        'onloadstart'        => true,
        'onmousedown'        => true,
        'onmousemove'        => true,
        'onmouseout'         => true,
        'onmouseover'        => true,
        'onmouseup'          => true,
        'onmousewheel'       => true,
        'onpause'            => true,
        'onplay'             => true,
        'onplaying'          => true,
        'onprogress'         => true,
        'onratechange'       => true,
        'onreadystatechange' => true,
        'onreset'            => true,
        'onscroll'           => true,
        'onseeked'           => true,
        'onseeking'          => true,
        'onselect'           => true,
        'onshow'             => true,
        'onstalled'          => true,
        'onsubmit'           => true,
        'onsuspend'          => true,
        'ontimeupdate'       => true,
        'onvolumechange'     => true,
        'onwaiting'          => true,
        'spellcheck'         => true,
        'style'              => true,
        'tabindex'           => true,
        'title'              => true,
        'xml:base'           => true,
        'xml:lang'           => true,
        'xml:space'          => true,
    );

    /**
     * Attributes valid for the tag represented by this helper
     *
     * This should be overridden in extending classes
     *
     * @var array
     */
    protected $validTagAttributes = array(
    );

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param  null|int|string                     $name
     * @param  array|Traversable|ButtonsOptions    $options
     * @param  array|Traversable|ColumnAttributes  $attributes
     * @param  array|Traversable|ColumnCondition[] $conditions
     * @return AbstractColumn
     */
    public function __construct($name = null, $options = null, $attributes = null, $conditions = null)
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

        if (null !== $conditions) {
            $this->setConditions($conditions);
        }
    }

    /**
     * This function is automatically called when creating column with factory. It
     * allows to perform various operations (add columns...)
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Prepare the grid column (mostly used for rendering purposes)
     *
     * @param  GridInterface $grid
     * @return mixed
     */
    public function prepareColumn(GridInterface $grid)
    {
        $filters = $grid->getParam('filters');
        $name = $this->getName();

        if (!empty($filters['rules'][$name])) {
            foreach ($filters['rules'][$name] as $index => $filterDefinition) {
                $operator = $filterDefinition['operator'];
                $operatorOutput = $grid->getPlatform()->getFilterOperatorOutput($operator);
                $value = $filterDefinition['value'];

                $this->getAttributes()->setSearchDataInit("function(elem) {
                console.log(elem);
                    $(elem).val('{$value}');
                    $(elem).parents('tr').find(\"[colname='{$name}']\").attr('soper', '{$operatorOutput}').text('{$operator}');
                }");
            }
        }
    }

    /**
     * Set options for an column. Accepted options are:
     * - label: label to associate with the column
     *
     * @param  array|Traversable $options
     * @return AbstractColumn|ColumnInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'The options parameter must be an array or a Traversable'
            );
        }

        if (isset($options['name'])) {
            $this->setName($options['name']);
        }

        if (isset($options['value'])) {
            $this->setValue($options['value']);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get defined options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return the specified option
     *
     * @param  string $option
     * @return null|mixed
     */
    public function getOption($option)
    {
        if (!isset($this->options[$option])) {
            return null;
        }

        return $this->options[$option];
    }

    /**
     * Set column attributes
     *
     * @param  array|\Traversable|ColumnAttributes $attributes
     * @throws Exception\InvalidArgumentException
     * @return AbstractColumn
     */
    public function setAttributes($attributes)
    {
        if (!$attributes instanceof ColumnAttributes) {
            if (is_object($attributes) && !$attributes instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                        'Expected instance of LemoGrid\Column\ColumnAttributes; '
                            . 'received "%s"', get_class($attributes))
                );
            }
            $attributes = new ColumnAttributes($attributes);
        }

        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get column attributes
     *
     * @return ColumnAttributes
     */
    public function getAttributes()
    {
        if (!$this->attributes) {
            $this->setAttributes(new ColumnAttributes());
        }

        return $this->attributes;
    }

    /**
     * Clear all attributes
     *
     * @return AbstractColumn|ColumnInterface
     */
    public function clearAttributes()
    {
        $this->attributes = new ColumnAttributes();
        return $this;
    }

    /**
     * @param  int|string            $key
     * @param  array|ColumnCondition $condition
     * @return AbstractColumn
     */
    public function addCondition($key, $condition)
    {
        if ($condition instanceof ColumnCondition) {
            $this->conditions[$key] = $condition;
        } elseif (is_array($condition)) {
            $this->conditions[$key] = new ColumnCondition($condition);
        } else {
            throw new Exception\InvalidArgumentException(
                'The conditions parameter must be an array or array of ColumnCondition'
            );
        }

        return $this;
    }

    /**
     * Set conditions for an column.
     *
     * @param  array|Traversable $conditions
     * @return AbstractColumn|ColumnInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setConditions(array $conditions)
    {
        foreach ($conditions as $key => $condition) {
            $this->addCondition($key, $condition);
        }

        return $this;
    }

    /**
     * Return the specified condition
     *
     * @param  string $condition
     * @return null|ColumnCondition
     */
    public function getCondition($condition)
    {
        if (!isset($this->conditions[$condition])) {
            return null;
        }

        return $this->conditions[$condition];
    }

    /**
     * Get defined conditions
     *
     * @return ColumnCondition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Clear conditions
     *
     * @return AbstractColumn|ColumnInterface
     */
    public function clearConditions()
    {
        $this->conditions = array();
        return $this;
    }

    /**
     * Set the column identifier
     *
     * @param  string $identifier
     * @return AbstractColumn
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Get the column identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        if(null === $this->identifier) {
            $this->identifier = $this->getName();
        }

        return $this->identifier;
    }

    /**
     * Set the column name
     *
     * @param  string $name
     * @return AbstractColumn
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the column name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the column value
     *
     * @param  string $value
     * @return AbstractColumn
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the column value
     *
     * @return string|array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param  array           $item
     * @param  AbstractAdapter $adapter
     * @throws Exception\InvalidArgumentException
     * @return bool
     */
    public function isValid(AbstractAdapter $adapter, array $item)
    {
        $conditions = $this->getConditions();
        $isValid = true;

        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                $value = $adapter->findValue($condition->getColumn(), $item);

                switch (strtolower($condition->getExpression())) {
                    case '|':
                        if (!is_array($condition->getValue()) || !in_array($value, $condition->getValue())) {
                            $isValid = false;
                        }
                        break;
                    case '=':
                    case '==':
                        if ($value != $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case '!=':
                        if ($value == $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case '>':
                        if ($value <= $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case '>=':
                        if ($value < $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case '<':
                        if ($value >= $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                    case '<=':
                        if ($value > $condition->getValue()) {
                            $isValid = false;
                        }
                        break;
                }
            }
        }

        return $isValid;
    }

    /**
     * Prepare attributes for rendering
     *
     * Ensures appropriate attributes are present (e.g., if "name" is present,
     * but no "id", sets the latter to the former).
     *
     * Removes any invalid attributes
     *
     * @param  array $attributes
     * @return array
     */
    protected function prepareAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $attribute = strtolower($key);

            if (!isset($this->validGlobalAttributes[$attribute])
                && !isset($this->validTagAttributes[$attribute])
                && 'data-' != substr($attribute, 0, 5)
            ) {
                // Invalid attribute for the current tag
                unset($attributes[$key]);
                continue;
            }

            // Normalize attribute key, if needed
            if ($attribute != $key) {
                unset($attributes[$key]);
                $attributes[$attribute] = $value;
            }

            // Normalize boolean attribute values
            if (isset($this->booleanAttributes[$attribute])) {
                $attributes[$attribute] = $this->prepareBooleanAttributeValue($attribute, $value);
            }
        }

        return $attributes;
    }

    /**
     * Prepare a boolean attribute value
     *
     * Prepares the expected representation for the boolean attribute specified.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return string
     */
    protected function prepareBooleanAttributeValue($attribute, $value)
    {
        if (!is_bool($value) && in_array($value, $this->booleanAttributes[$attribute])) {
            return $value;
        }

        $value = (bool) $value;
        return ($value
            ? $this->booleanAttributes[$attribute]['on']
            : $this->booleanAttributes[$attribute]['off']
        );
    }

    /**
     * Create a string of all attribute/value pairs
     *
     * Escapes all attribute values
     *
     * @param  array $attributes
     * @return string
     */
    public function createAttributesString(array $attributes)
    {
        $attributes = $this->prepareAttributes($attributes);
        $strings    = array();
        foreach ($attributes as $key => $value) {
            $key = strtolower($key);
            if (!$value && isset($this->booleanAttributes[$key])) {
                // Skip boolean attributes that expect empty string as false value
                if ('' === $this->booleanAttributes[$key]['off']) {
                    continue;
                }
            }

            $strings[] = sprintf('%s="%s"', $key, $value);
        }
        return implode(' ', $strings);
    }
}
