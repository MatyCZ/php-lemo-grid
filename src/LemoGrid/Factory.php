<?php

namespace LemoGrid;

use ArrayAccess;
use LemoGrid\Column\ColumnInterface;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Hydrator;

class Factory
{
    /**
     * @var GridColumnManager
     */
    protected $gridColumnManager;

    /**
     * @param GridColumnManager $gridColumnManager
     */
    public function __construct(GridColumnManager $gridColumnManager = null)
    {
        if ($gridColumnManager) {
            $this->setGridColumnManager($gridColumnManager);
        }
    }

    /**
     * Set the grid column manager
     *
     * @param  GridColumnManager $gridColumnManager
     * @return Factory
     */
    public function setGridColumnManager(GridColumnManager $gridColumnManager)
    {
        $this->gridColumnManager = $gridColumnManager;
        return $this;
    }

    /**
     * Get grid column manager
     *
     * @return GridColumnManager
     */
    public function getGridColumnManager()
    {
        if ($this->gridColumnManager === null) {
            $this->setGridColumnManager(new GridColumnManager());
        }

        return $this->gridColumnManager;
    }

    /**
     * Create an column or grid
     *
     * Introspects the 'type' key of the provided $spec, and determines what
     * type is being requested; if none is provided, assumes the spec
     * represents simply an column.
     *
     * @param  array|Traversable $spec
     * @return ColumnInterface
     * @throws Exception\DomainException
     */
    public function create($spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);
        $type = isset($spec['type']) ? $spec['type'] : 'LemoGrid\Column';

        $column = $this->getGridColumnManager()->get($type);

        if ($column instanceof GridInterface) {
            return $this->configureGrid($column, $spec);
        }

        if ($column instanceof ColumnInterface) {
            return $this->configureColumn($column, $spec);
        }

        throw new Exception\DomainException(sprintf(
            '%s expects the $spec["type"] to implement one of %s, %s, or %s; received %s',
            __METHOD__,
            'LemoGrid\ColumnInterface',
            'LemoGrid\GridInterface',
            $type
        ));
    }

    /**
     * Create a column
     *
     * @param  array $spec
     * @return ColumnInterface
     */
    public function createColumn($spec)
    {
        if (!isset($spec['type'])) {
            $spec['type'] = 'LemoGrid\Column';
        }

        return $this->create($spec);
    }

    /**
     * Create a grid
     *
     * @param  array $spec
     * @return ColumnInterface
     */
    public function createGrid($spec)
    {
        if (!isset($spec['type'])) {
            $spec['type'] = 'LemoGrid\Grid';
        }

        return $this->create($spec);
    }

    /**
     * Configure an column based on the provided specification
     *
     * Specification can contain any of the following:
     * - type: the Column class to use; defaults to \LemoGrid\Column
     * - name: what name to provide the column, if any
     * - options: an array, Traversable, or ArrayAccess object of column options
     * - attributes: an array, Traversable, or ArrayAccess object of column
     *   attributes to assign
     *
     * @param  ColumnInterface              $column
     * @param  array|Traversable|ArrayAccess $spec
     * @throws Exception\DomainException
     * @return ColumnInterface
     */
    public function configureColumn(ColumnInterface $column, $spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);

        $name       = isset($spec['name'])       ? $spec['name']       : null;
        $identifier = isset($spec['identifier']) ? $spec['identifier'] : null;
        $options    = isset($spec['options'])    ? $spec['options']    : null;
        $attributes = isset($spec['attributes']) ? $spec['attributes'] : null;

        if ($name !== null && $name !== '') {
            $column->setName($name);
            $attributes['name'] = $name;
        }

        if ($identifier !== null && $identifier !== '') {
            $column->setIdentifier($identifier);
        }

        if (is_array($options) || $options instanceof Traversable || $options instanceof ArrayAccess) {
            $column->setOptions($options);
        }

        if (is_array($attributes) || $attributes instanceof Traversable || $attributes instanceof ArrayAccess) {
            $column->setAttributes($attributes);
        }

        return $column;
    }

    /**
     * Validate a provided specification
     *
     * Ensures we have an array, Traversable, or ArrayAccess object, and returns it.
     *
     * @param  array|Traversable|ArrayAccess $spec
     * @param  string $method Method invoking the validator
     * @return array|ArrayAccess
     * @throws Exception\InvalidArgumentException for invalid $spec
     */
    protected function validateSpecification($spec, $method)
    {
        if (is_array($spec)) {
            return $spec;
        }

        if ($spec instanceof Traversable) {
            $spec = ArrayUtils::iteratorToArray($spec);
            return $spec;
        }

        if (!$spec instanceof ArrayAccess) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array, or object implementing Traversable or ArrayAccess; received "%s"',
                $method,
                (is_object($spec) ? get_class($spec) : gettype($spec))
            ));
        }

        return $spec;
    }

    /**
     * Takes a list of column specifications, creates the columns, and injects them into the provided grid
     *
     * @param  array|Traversable|ArrayAccess $columns
     * @param  GridInterface $grid
     * @param  string $method Method invoking this one (for exception messages)
     * @return void
     */
    protected function prepareAndInjectColumns($columns, GridInterface $grid, $method)
    {
        $columns = $this->validateSpecification($columns, $method);

        foreach ($columns as $columnSpecification) {
            $flags = isset($columnSpecification['flags']) ? $columnSpecification['flags'] : array();
            $spec  = isset($columnSpecification['spec'])  ? $columnSpecification['spec']  : array();

            if (!isset($spec['type'])) {
                $spec['type'] = 'LemoGrid\Column';
            }

            $column = $this->create($spec);
            $grid->add($column, $flags);
        }
    }
}
