<?php

namespace LemoGrid;

use ArrayAccess;
use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\Column\ColumnInterface;
use LemoGrid\Export\ExportInterface;
use LemoGrid\Platform\PlatformInterface;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Hydrator;

class Factory
{
    /**
     * @var GridAdapterManager
     */
    protected $gridAdapterManager;

    /**
     * @var GridColumnManager
     */
    protected $gridColumnManager;

    /**
     * @var GridExportManager
     */
    protected $gridExportManager;

    /**
     * @var GridPlatformManager
     */
    protected $gridPlatformManager;

    /**
     * @param GridPlatformManager $gridPlatformManager
     * @param GridAdapterManager $gridAdapterManager
     * @param GridColumnManager $gridColumnManager
     * @param GridExportManager $gridExportManager
     */
    public function __construct(GridPlatformManager $gridPlatformManager = null, GridAdapterManager $gridAdapterManager = null, GridColumnManager $gridColumnManager = null, GridExportManager $gridExportManager = null)
    {
        if (null !== $gridPlatformManager) {
            $this->setGridPlatformManager($gridPlatformManager);
        }

        if (null !== $gridAdapterManager) {
            $this->setGridAdapterManager($gridAdapterManager);
        }

        if (null !== $gridColumnManager) {
            $this->setGridColumnManager($gridColumnManager);
        }

        if (null !== $gridExportManager) {
            $this->setGridExportManager($gridExportManager);
        }
    }

    /**
     * Set the grid adapter manager
     *
     * @param  GridAdapterManager $gridAdapterManager
     * @return Factory
     */
    public function setGridAdapterManager(GridAdapterManager $gridAdapterManager)
    {
        $this->gridAdapterManager = $gridAdapterManager;

        return $this;
    }

    /**
     * Get grid adapter manager
     *
     * @return GridAdapterManager
     */
    public function getGridAdapterManager()
    {
        if ($this->gridAdapterManager === null) {
            $this->setGridAdapterManager(new GridAdapterManager());
        }

        return $this->gridAdapterManager;
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
        if (null === $this->gridColumnManager) {
            $this->setGridColumnManager(new GridColumnManager());
        }

        return $this->gridColumnManager;
    }

    /**
     * Set the grid export manager
     *
     * @param  GridExportManager $gridExportManager
     * @return Factory
     */
    public function setGridExportManager(GridExportManager $gridExportManager)
    {
        $this->gridExportManager = $gridExportManager;

        return $this;
    }

    /**
     * Get grid export manager
     *
     * @return GridExportManager
     */
    public function getGridExportManager()
    {
        if (null === $this->gridExportManager) {
            $this->setGridExportManager(new GridExportManager());
        }

        return $this->gridExportManager;
    }

    /**
     * Set the grid platform manager
     *
     * @param  GridPlatformManager $gridPlatformManager
     * @return Factory
     */
    public function setGridPlatformManager(GridPlatformManager $gridPlatformManager)
    {
        $this->gridPlatformManager = $gridPlatformManager;

        return $this;
    }

    /**
     * Get grid platform manager
     *
     * @return GridPlatformManager
     */
    public function getGridPlatformManager()
    {
        if ($this->gridPlatformManager === null) {
            $this->setGridPlatformManager(new GridPlatformManager());
        }

        return $this->gridPlatformManager;
    }

    /**
     * Create an adapter
     *
     * @param  array $spec
     * @throws Exception\DomainException
     * @return AdapterInterface
     */
    public function createAdapter($spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);
        if (!isset($spec['type'])) {
            $spec['type'] = 'LemoGrid\Adapter';
        }

        $adapter = $this->getGridAdapterManager()->get($spec['type']);

        if ($adapter instanceof AdapterInterface) {
            return $this->configureAdapter($adapter, $spec);
        }

        throw new Exception\DomainException(sprintf(
            '%s expects the $spec["type"] to implement one of %s, %s, or %s; received %s',
            __METHOD__,
            'LemoGrid\Adapter\AdapterInterface',
            $spec['type']
        ));
    }

    /**
     * Create a column
     *
     * @param  array|Traversable $spec
     * @return ColumnInterface
     * @throws Exception\DomainException
     */
    public function createColumn($spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);
        $type = isset($spec['type']) ? $spec['type'] : 'LemoGrid\Column';

        $column = $this->getGridColumnManager()->get($type);

        if ($column instanceof ColumnInterface) {
            return $this->configureColumn($column, $spec);
        }

        throw new Exception\DomainException(sprintf(
            '%s expects the $spec["type"] to implement one of %s, %s, or %s; received %s',
            __METHOD__,
            'LemoGrid\Column\ColumnInterface',
            $spec['type']
        ));
    }

    /**
     * Create a grid
     *
     * @param  array $spec
     * @throws Exception\DomainException
     * @return GridInterface
     */
    public function createGrid($spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);

        return $this->configureGrid(new Grid(), $spec);
    }

    /**
     * Create a export
     *
     * @param  array $spec
     * @throws Exception\DomainException
     * @return ExportInterface
     */
    public function createExport($spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);
        if (!isset($spec['type'])) {
            $spec['type'] = 'LemoGrid\Export';
        }

        $export = $this->getGridExportManager()->get($spec['type']);

        if ($export instanceof ExportInterface) {
            return $this->configureExport($export, $spec);
        }

        throw new Exception\DomainException(sprintf(
            '%s expects the $spec["type"] to implement one of %s, %s, or %s; received %s',
            __METHOD__,
            'LemoGrid\Export\ExportInterface',
            $spec['type']
        ));
    }

    /**
     * Create a platform
     *
     * @param  array $spec
     * @throws Exception\DomainException
     * @return PlatformInterface
     */
    public function createPlatform($spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);
        if (!isset($spec['type'])) {
            $spec['type'] = 'LemoGrid\Platform';
        }

        $platform = $this->getGridPlatformManager()->get($spec['type']);

        if ($platform instanceof PlatformInterface) {
            return $this->configurePlatform($platform, $spec);
        }

        throw new Exception\DomainException(sprintf(
            '%s expects the $spec["type"] to implement one of %s, %s, or %s; received %s',
            __METHOD__,
            'LemoGrid\Platform\PlatformInterface',
            $spec['type']
        ));
    }

    /**
     * Configure an adapter based on the provided specification
     *
     * Specification can contain any of the following:
     * - options: an array, Traversable, or ArrayAccess object of adapter options
     *
     * @param  AdapterInterface              $adapter
     * @param  array|Traversable|ArrayAccess $spec
     * @throws Exception\DomainException
     * @return AdapterInterface
     */
    public function configureAdapter(AdapterInterface $adapter, $spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);

        $options = isset($spec['options']) ? $spec['options'] : null;

        if ($adapter instanceof AdapterOptionsInterface && (is_array($options) || $options instanceof Traversable || $options instanceof ArrayAccess)) {
            $adapter->setOptions($options);
        }

        return $adapter;
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
        $conditions = isset($spec['conditions']) ? $spec['conditions'] : null;

        if ($name !== null && $name !== '') {
            $column->setName($name);
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

        if (is_array($conditions) || $conditions instanceof Traversable || $conditions instanceof ArrayAccess) {
            $column->setConditions($conditions);
        }

        return $column;
    }

    /**
     * Configure a grid based on the provided specification
     *
     * Specification can contain any of the following:
     * - type: the Grid class to use; defaults to \LemoGrid\Grid
     * - name: what name to provide the grid, if any
     * - adapter: adapter instance, named adapter class
     * - columns: an array or Traversable object where each entry is an array
     *   or ArrayAccess object containing the keys:
     *   - flags: (optional) array of flags to pass to GridInterface::add()
     *   - spec: the actual column specification, per {@link configureColumn()}
     * - platform: platform instance, named platform class
     *
     * @param  GridInterface                 $grid
     * @param  array|Traversable|ArrayAccess $spec
     * @throws Exception\DomainException
     * @return GridInterface
     */
    public function configureGrid(GridInterface $grid, $spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);

        $name = isset($spec['name']) ? $spec['name'] : null;

        if ($name !== null && $name !== '') {
            $grid->setName($name);
        }

        if (isset($spec['adapter'])) {
            $this->prepareAndInjectAdapter($spec['adapter'], $grid, __METHOD__);
        }

        if (isset($spec['columns'])) {
            $this->prepareAndInjectColumns($spec['columns'], $grid, __METHOD__);
        }

        if (isset($spec['platform'])) {
            $this->prepareAndInjectPlatform($spec['platform'], $grid, __METHOD__);
        }

        return $grid;
    }

    /**
     * Configure an export based on the provided specification
     *
     * Specification can contain any of the following:
     * - options: an array, Traversable, or ArrayAccess object of export options
     *
     * @param  ExportInterface              $export
     * @param  array|Traversable|ArrayAccess $spec
     * @throws Exception\DomainException
     * @return ExportInterface
     */
    public function configureExport(ExportInterface $export, $spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);

        $options = isset($spec['options'])? $spec['options'] : null;

        if (is_array($options) || $options instanceof Traversable || $options instanceof ArrayAccess) {
            $export->setOptions($options);
        }

        return $export;
    }

    /**
     * Configure an platform based on the provided specification
     *
     * Specification can contain any of the following:
     * - options: an array, Traversable, or ArrayAccess object of platform options
     *
     * @param  PlatformInterface              $platform
     * @param  array|Traversable|ArrayAccess $spec
     * @throws Exception\DomainException
     * @return PlatformInterface
     */
    public function configurePlatform(PlatformInterface $platform, $spec)
    {
        $spec = $this->validateSpecification($spec, __METHOD__);

        $options = isset($spec['options'])? $spec['options'] : null;

        if (is_array($options) || $options instanceof Traversable || $options instanceof ArrayAccess) {
            $platform->setOptions($options);
        }

        return $platform;
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
     * Prepare and inject a named adapter
     *
     * Takes a string indicating a adapter class name (or a concrete instance), try first to instantiates the class
     * by pulling it from service manager, and injects the adapter instance into the form.
     *
     * @param  string|array|Adapter\AdapterInterface $adapterOrName
     * @param  GridInterface                         $grid
     * @param  string                                $method
     * @return void
     * @throws Exception\DomainException If $adapterOrName is not a string, does not resolve to a known class, or
     *                                   the class does not implement Adapter\AdapterInterface
     */
    protected function prepareAndInjectAdapter($adapterOrName, GridInterface $grid, $method)
    {
        if (is_object($adapterOrName) && $adapterOrName instanceof Adapter\AdapterInterface) {
            $grid->setAdapter($adapterOrName);
            return;
        }

        if (is_array($adapterOrName)) {
            if (!isset($adapterOrName['type'])) {
                throw new Exception\DomainException(sprintf(
                    '%s expects array specification to have a type value',
                    $method
                ));
            }
            $adapterOptions = (isset($adapterOrName['options'])) ? $adapterOrName['options'] : array();
            $adapterOrName = $adapterOrName['type'];
        } else {
            $adapterOptions = array();
        }

        if (is_string($adapterOrName)) {
            $adapter = $this->getAdapterFromName($adapterOrName);
        }

        if (!$adapter instanceof Adapter\AdapterInterface) {
            throw new Exception\DomainException(sprintf(
                '%s expects a valid implementation of LemoGrid\Adapter\AdapterInterface; received "%s"',
                $method,
                $adapterOrName
            ));
        }

        if (!empty($adapterOptions) && $adapter instanceof Adapter\AdapterOptionsInterface) {
            $adapter->setOptions($adapterOptions);
        }

        $grid->setAdapter($adapter);
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
            $column = $this->createColumn($spec);
            $grid->add($column, $flags);
        }
    }

    /**
     * Prepare and inject a named platform
     *
     * Takes a string indicating a platform class name (or a concrete instance), try first to instantiates the class
     * by pulling it from service manager, and injects the platform instance into the form.
     *
     * @param  string|array|Platform\PlatformInterface $platformOrName
     * @param  GridInterface                           $grid
     * @param  string                                  $method
     * @return void
     * @throws Exception\DomainException If $platformOrName is not a string, does not resolve to a known class, or
     *                                   the class does not implement Platform\PlatformInterface
     */
    protected function prepareAndInjectPlatform($platformOrName, GridInterface $grid, $method)
    {
        if (is_object($platformOrName) && $platformOrName instanceof Platform\PlatformInterface) {
            $grid->setPlatform($platformOrName);
            return;
        }

        if (is_array($platformOrName)) {
            if (!isset($platformOrName['type'])) {
                throw new Exception\DomainException(sprintf(
                    '%s expects array specification to have a type value',
                    $method
                ));
            }
            $platformOptions = (isset($platformOrName['options'])) ? $platformOrName['options'] : array();
            $platformOrName = $platformOrName['type'];
        } else {
            $platformOptions = array();
        }

        if (is_string($platformOrName)) {
            $platform = $this->getPlatformFromName($platformOrName);
        }

        if (!$platform instanceof Platform\PlatformInterface) {
            throw new Exception\DomainException(sprintf(
                '%s expects a valid implementation of LemoGrid\Platform\PlatformInterface; received "%s"',
                $method,
                $platformOrName
            ));
        }

        $platform->setOptions($platformOptions);
        $grid->setPlatform($platform);
    }

    /**
     * Try to pull adapter from service manager, or instantiates it from its name
     *
     * @param  string $adapterName
     * @return mixed
     * @throws Exception\DomainException
     */
    protected function getAdapterFromName($adapterName)
    {
        $serviceLocator = $this->getGridAdapterManager()->getServiceLocator();

        if ($serviceLocator && $serviceLocator->has($adapterName)) {
            return $serviceLocator->get($adapterName);
        }

        if (!class_exists($adapterName)) {
            throw new Exception\DomainException(sprintf(
                'Expects string adapter name to be a valid class name; received "%s"',
                $adapterName
            ));
        }

        $adapter = new $adapterName;
        return $adapter;
    }

    /**
     * Try to pull export from service manager, or instantiates it from its name
     *
     * @param  string $exportName
     * @return mixed
     * @throws Exception\DomainException
     */
    protected function getExportFromName($exportName)
    {
        $serviceLocator = $this->getGridExportManager()->getServiceLocator();

        if ($serviceLocator && $serviceLocator->has($exportName)) {
            return $serviceLocator->get($exportName);
        }

        if (!class_exists($exportName)) {
            throw new Exception\DomainException(sprintf(
                'Expects string export name to be a valid class name; received "%s"',
                $exportName
            ));
        }

        $export = new $exportName;
        return $export;
    }

    /**
     * Try to pull platform from service manager, or instantiates it from its name
     *
     * @param  string $platformName
     * @return mixed
     * @throws Exception\DomainException
     */
    protected function getPlatformFromName($platformName)
    {
        $serviceLocator = $this->getGridPlatformManager()->getServiceLocator();

        if ($serviceLocator && $serviceLocator->has($platformName)) {
            return $serviceLocator->get($platformName);
        }

        if (!class_exists($platformName)) {
            throw new Exception\DomainException(sprintf(
                'Expects string platform name to be a valid class name; received "%s"',
                $platformName
            ));
        }

        $platform = new $platformName;
        return $platform;
    }
}
