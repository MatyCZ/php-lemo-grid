<?php

/**
 * @namespace
 */
namespace LemoBase\Grid;

use LemoBase\Grid\Adapter\AdapterInterface;
use Traversable;
use Zend\Config\Config;
use Zend\Registry;
use Zend\Stdlib\IteratorToArray;
use Zend\Translator;
use Zend\View;
use Zend\ServiceManager\ServiceLocatorInterface;

class Grid
{
    const REQUEST_TYPE_GET  = 'get';
    const REQUEST_TYPE_POST = 'post';

    const DATATYPE_JAVASCRIPT = 'javascript';
    const DATATYPE_JSON       = 'json';
    const DATATYPE_JSONSTRING = 'jsonstring';
    const DATATYPE_LOCAL      = 'local';
    const DATATYPE_XML        = 'xml';
    const DATATYPE_XMLSTRING  = 'xmlstring';

    const SORT_ORDER_ASC  = 'asc';
    const SORT_ORDER_DESC = 'desc';

    /**
     * Grid adapter
     *
     * @var string
     */
    protected $_adapter;

    /**
     * Grid metadata and attributes
     *
     * @var array
     */
    protected $_attribs = array();

    /**
     * Column plugin manager
     *
     * @var ColumnPluginManager
     */
    protected static $_columnPluginManager = null;

    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * Grid order
     *
     * @var int|null
     */
    protected $_gridOrder;

    /**
     * Name of grid
     *
     * @var string
     */
    protected $_name = null;

    /**
     * Order in which to display and iterate columns
     *
     * @var array
     */
    protected $_order = array();

    /**
     * Whether internal order has been updated or not
     *
     * @var bool
     */
    protected $_orderUpdated = false;

    /**
     * Parameter container responsible for query parameters
     *
     * @var \Zend\Stdlib\Parameters
     */
    protected $_queryParams = null;

    /**
     * Request object
     *
     * @var \Zend\Http\PhpEnvironment\Request
     */
    protected $_request = null;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * @var \Zend\Session\SessionManager
     */
    protected $_session = null;

    /**
     * @var string
     */
    protected $_sessionNamespace = 'lemoLibrary_grid';

    /**
     * @var View
     */
    protected $_view;

    /**
     * List of grid options
     *
     * @var array
     */
    private $_gridOptions = array(
        'alternativeRows' => 'altRows',
        'alternativeRowsClass' => 'altclass',
        'autoEncodeIncomingAndPostData' => 'autoencode',
        'autowidth' => null,
        'caption' => null,
        'cellLayout' => null,
        'cellEdit' => null,
        'cellEditUrl' => 'editurl',
        'cellSaveType' => 'cellsubmit',
        'cellSaveUrl' => 'cellurl',
        'dataString' => null,
        'dataType' => null,
        'defaultPage' => 'page',
        'defaultSortColumn' => 'sortname',
        'defaultSortOrder' => 'sortorder',
        'expandColumnOnClick' => 'ExpandColClick',
        'expandColumnIdentifier' => 'ExpandColumn',
        'forceFit' => null,
        'gridState' => null,
        'grouping' => null,
        'headerTitles' => null,
        'height' => null,
        'hoverRows' => null,
        'loadOnce' => null,
        'loadType' => 'loadui',
        'multiSelect' => null,
        'multiSelectKey' => 'multikey',
        'multiSelectWidth' => null,
        'pagerElementId' => 'pager',
        'pagerPosition' => 'pagerpos',
        'pagerShowButtions' => 'pgbuttons',
        'pagerShowInput' => 'pginput',
        'requestType' => 'mtype',
        'renderFooterRow' => 'footerrow',
        'renderRecordsInfo' => 'viewrecords',
        'renderRowNumbersColumn' => 'rownumbers',
        'resizeClass' => null,
        'recordsPerPage' => 'rowNum',
        'recordsPerPageList' => 'rowList',
        'scroll' => null,
        'scrollOffset' => null,
        'scrollRows' => null,
        'scrollTimeout' => null,
        'shrinkToFit' => null,
        'sortingColumns' => 'sortable',
        'sortingColumnsDefinition' => 'viewsortcols',
        'shrinkToFit' => null,
        'treeGrid' => null,
        'treeGridType' => 'treeGridModel',
        'treeGridIcons' => 'treeIcons',
        'url' => null,
        'width' => null,
    );

    // ===== GRID PROPERTIES =====


    /**
     * Set a zebra-striped grid.
     *
     * @var bool
     */
    protected $_alternativeRows = null;

    /**
     * The class that is used for alternate (zebra) rows. You can construct your own class and replace this value.
     * This option is valid only if altRows options is set to true.
     *
     * @var string
     */
    protected $_alternativeRowsClass = null;

    /**
     * When set to true encodes (html encode) the incoming (from server) and posted data (from editing modules).
     * By example < will be converted to &lt;
     *
     * @var bool
     */
    protected $_autoEncodeIncomingAndPostData = null;

    /**
     * When set to true, the grid width is recalculated automatically to the width of the parent element. This is done
     * only initially when the grid is created. In order to resize the grid when the parent element changes width you
     * should apply custom code and use the setGridWidth method for this purpose.
     *
     * @var bool
     */
    protected $_autowidth = true;

    /**
     * Defines the Caption layer for the grid. This caption appears above the Header layer. If the string is empty
     * the caption does not appear.
     *
     * @var string
     */
    protected $_caption = null;

    /**
     * This option determines the padding + border width of the cell. Usually this should not be changed, but if custom
     * changes to td element are made in the grid css file this will need to be changed. The initial value of 5 means
     * paddingLef⇒2+paddingRight⇒2+borderLeft⇒1=5.
     *
     * @var int
     */
    protected $_cellLayout = null;

    /**
     * Enables (disables) cell editing. See Cell Editing for more details.
     *
     * @var bool
     */
    protected $_cellEdit = null;

    /**
     * Defines the url for inline and form editing.
     *
     * @var string
     */
    protected $_cellEditUrl = null;

    /**
     * Determines where the contents of the cell are saved: 'remote' or 'clientArray'.
     *
     * @var string
     */
    protected $_cellSaveType = null;

    /**
     * The url where the cell is to be saved.
     *
     * @var string
     */
    protected $_cellSaveUrl = null;

    /**
     * A array that store the local data passed to the grid. You can directly point to this variable in case you want
     * to load a array data. It can replace addRowData method which is slow on relative big data.
     *
     * @var array
     */
    protected $_data = null;

    /**
     * @var string
     */
    protected $_dataString = null;

    /**
     * Defines what type of information to expect to represent data in the grid. Valid options are xml - we expect
     * xml data; xmlstring - we expect xml data as string; json - we expect JSON data; jsonstring - we expect JSON data
     * as string; local - we expect data defined at client side (array data); javascript - we expect javascript as data;
     * function - custom defined function for retrieving data.
     *
     * @var string
     */
    protected $_dataType = self::DATATYPE_JSON;

    /**
     * Set the initial number of page when we make the request.This parameter is passed to the url for use by the server
     * routine retrieving the data
     *
     * @var int
     */
    protected $_defaultPage = null;

    /**
     * The initial sorting name when we use datatypes xml or json (data returned from server). This parameter is added
     * to the url. If set and the index (name) match the name from colModel then to this column by default is added
     * a image sorting icon, according to the parameter sortorder (below). See prmNames.
     *
     * @var string
     */
    protected $_defaultSortColumn = null;

    /**
     * The initial sorting order when we use datatypes xml or json (data returned from server).This parameter is added
     * to the url - see prnNames. Two possible values - asc or desc.
     *
     * @var string
     */
    protected $_defaultSortOrder = self::SORT_ORDER_ASC;

    /**
     * Enables grouping in grid.
     *
     * @var bool
     */
    protected $_grouping = null;

    /**
     * When set to false the mouse hovering is disabled in the grid data rows.
     *
     * @var bool
     */
    protected $_hoverRows = null;

    /**
     * Indicates which column should be used to expand the tree grid. If not set the first one
     * is used. Valid only when treeGrid option is set to true.
     *
     * @var string
     */
    protected $_expandColumnIdentifier = null;

    /**
     * When true, the treeGrid is expanded and/or collapsed when we click on the text of the expanded column, not
     * only on the image
     *
     * @var bool
     */
    protected $_expandColumnOnClick = null;

    protected $_filterToolbar = array(
        'enabled' => true,
        'stringResult' => true,
        'searchOnEnter' => false
    );

    /**
     * If set to true, and resizing the width of a column, the adjacent column (to the right) will resize so that
     * the overall grid width is maintained (e.g., reducing the width of column 2 by 30px will increase the size of
     * column 3 by 30px). In this case there is no horizontal scrolbar. Note: this option is not compatible with
     * shrinkToFit option - i.e if shrinkToFit is set to false, forceFit is ignored.
     *
     * @var bool
     */
    protected $_forceFit = null;

    /**
     * Determines the current state of the grid (i.e. when used with hiddengrid, hidegrid and caption options). Can
     * have either of two states: 'visible' or 'hidden'
     *
     * @var string
     */
    protected $_gridState = null;

    /**
     * If the option is set to true the title attribute is added to the column headers.
     *
     * @var string
     */
    protected $_headerTitles = null;

    /**
     * The height of the grid. Can be set as number (in this case we mean pixels) or as percentage
     * (only 100% is acceped) or value of auto is acceptable.
     *
     * @var string
     */
    protected $_height = null;

    /**
     * If this flag is set to true, the grid loads the data from the server only once (using the appropriate datatype).
     * After the first request the datatype parameter is automatically changed to local and all further manipulations
     * are done on the client side. The functions of the pager (if present) are disabled.
     *
     * @var bool
     */
    protected $_loadOnce = null;

    /**
     * This option controls what to do when an ajax operation is in progress.
     * 'disable', 'enable' or 'block'
     *
     * @var string
     */
    protected $_loadType = null;

    /**
     * If this flag is set to true a multi selection of rows is enabled. A new column at left side is added. Can be used
     * with any datatype option.
     *
     * @var bool
     */
    protected $_multiSelect = null;

    /**
     * This parameter have sense only multiselect option is set to true. Defines the key which will be pressed when we
     * make multiselection. The possible values are: shiftKey - the user should press Shift Key altKey - the user should
     * press Alt Key ctrlKey - the user should press Ctrl Key
     *
     * 'shiftKey', 'altKey', 'ctrlKey'
     *
     * @var string
     */
    protected $_multiSelectKey = null;

    /**
     * Determines the width of the multiselect column if multiselect is set to true.
     *
     * @var int
     */
    protected $_multiSelectWidth = null;

    /**
     * Defines that we want to use a pager bar to navigate through the records. This must be a valid html element;
     * in our example we gave the div the id of “pager”, but any name is acceptable. Note that the Navigation layer
     * (the “pager” div) can be positioned anywhere you want, determined by your html; in our example we specified that
     * the pager will appear after the Table Body layer.
     *
     * @var string
     */
    protected $_pagerElementId = null;

    /**
     * Determines the position of the pager in the grid. By default the pager element when created is divided in 3 parts
     * (one part for pager, one part for navigator buttons and one part for record information)
     *
     * 'left', 'center' or 'right'
     *
     * @var string
     */
    protected $_pagerPosition = null;

    /**
     * Determines if the Pager buttons should be shown if pager is available. Also valid only if pager is set correctly.
     * The buttons are placed in the pager bar.
     *
     * @var bool
     */
    protected $_pagerShowButtons = null;

    /**
     * Determines if the input box, where the user can change the number of requested page, should be available.
     * The input box appear in the pager bar.
     *
     * @var bool
     */
    protected $_pagerShowInput = null;

    /**
     * Determines the position of the record information in the pager.
     *
     * 'left', 'center' or 'right'
     *
     * @var string
     */
    protected $_recordPosition = null;

    /**
     * Defines the type of request to make ('post' or 'get')
     *
     * @var string
     */
    protected $_requestType = self::REQUEST_TYPE_GET;

    /**
     * If set to true this will place a footer table with one row below the gird records and above the pager.
     *
     * @var bool
     */
    protected $_renderFooterRow = null;

    /**
     * Enables or disables the show/hide grid button, which appears on the right side of the Caption layer. Takes effect
     * only if the caption property is not an empty string.
     *
     * @var bool
     */
    protected $_renderHideGridButton = null;

    /**
     * If this option is set to true, a new column at left of the grid is added. The purpose of this column is to count
     * the number of available rows, beginning from 1. In this case colModel is extended automatically with new element
     * with name - 'rn'. Also, be careful not to use the name 'rn'.
     *
     * @var bool
     */
    protected $_renderRowNumbersColumn = null;

    /**
     * If true, jqGrid displays the beginning and ending record number in the grid, out of the total number of records
     * in the query. This information is shown in the pager bar (bottom right by default)in this format:
     * “View X to Y out of Z”. If this value is true, there are other parameters that can be adjusted,
     * including 'emptyrecords' and 'recordtext'.
     *
     * @var bool
     */
    protected $_renderRecordsInfo = true;

    /**
     * Assigns a class to columns that are resizable so that we can show a resize handle only for ones that are
     * resizable.
     *
     * @var string
     */
    protected $_resizeClass = null;

    /**
     * Sets how many records we want to view in the grid. This parameter is passed to the url for use by the server
     * routine retrieving the data. Note that if you set this parameter to 10 (i.e. retrieve 10 records) and your server
     * return 15 then only 10 records will be loaded.
     *
     * @var int
     */
    protected $_recordsPerPage = 25;

    /**
     * An array to construct a select box element in the pager in which we can change the number of the visible rows.
     * When changed during the execution, this parameter replaces the rowNum parameter that is passed to the url.
     * If the array is empty the element does not appear in the pager. Typical you can set this like [10,20,30].
     * If the rowNum parameter is set to 30 then the selected value in the select box is 30.
     *
     * @var array
     */
    protected $_recordsPerPageList = array(5,10,25,50);

    /**
     * Creates dynamic scrolling grids. When enabled, the pager elements are disabled and we can use the vertical
     * scrollbar to load data. When set to true the grid will always hold all the items from the start through to the
     * latest point ever visited. When scroll is set to value (eg 1), the grid will just hold the visible lines. This
     * allow us to load the data at portions whitout to care about the memory leaks. Additionally this we have optional
     * extension to the server protocol: npage (see prmNames array). If you set the npage option in prmNames, then the
     * grid will sometimes request more than one page at a time, if not it will just perform multiple gets.
     *
     * @var bool|int
     */
    protected $_scroll = null;

    /**
     * Determines the width of the vertical scrollbar. Since different browsers interpret this width differently
     * (and it is difficult to calculate it in all browsers) this can be changed.
     *
     * @var int
     */
    protected $_scrollOffset = null;

    /**
     * When enabled, selecting a row with setSelection scrolls the grid so that the selected row is visible. This is
     * especially useful when we have a verticall scrolling grid and we use form editing with navigation buttons
     * (next or previous row). On navigating to a hidden row, the grid scrolls so the selected row becomes visible.
     *
     * @var bool
     */
    protected $_scrollRows = null;

    /**
     * This control the timeout handler when scroll is set to 1. In miliseconds.
     *
     * @var int
     */
    protected $_scrollTimeout = null;

    /**
     * This option describes the type of calculation of the initial width of each column against with the width of the
     * grid. If the value is true and the value in width option is set then: Every column width is scaled according to
     * the defined option width. Example: if we define two columns with a width of 80 and 120 pixels, but want the grid
     * to have a 300 pixels - then the columns are recalculated as follow:
     * 1- column = 300(new width)/200(sum of all width)*80(column width) = 120 and 2 column = 300/200*120 = 180.
     *
     * The grid width is 300px. If the value is false and the value in width option is set then:
     * The width of the grid is the width set in option. The column width are not recalculated and have the values
     * defined in colModel. If integer is set, the width is calculated according to it.
     *
     * @var bool
     */
    protected $_shrinkToFit = null;

    /**
     * When enabled this option allow column reordering with mouse. Since this option uses jQuery UI sortable widget,
     * be a sure that this widget and the related to widget files are loaded in head tag. Also be a sure too that you
     * mark the grid.jqueryui.js when you download the jqGrid.
     *
     * @var bool
     */
    protected $_sortingColumns = null;

    /**
     * The purpose of this parameter is to define different look and behavior of sorting icons that appear near the header.
     * This parameter is array with the following default options viewsortcols : [false,'vertical',true].
     *
     * The first parameter determines if all icons should be viewed at the same time when all columns have sort property
     * set to true. The default of false determines that only the icons of the current sorting column should be viewed.
     * Setting this parameter to true causes all icons in all sortable columns to be viewed.
     *
     * The second parameter determines how icons should be placed - vertical means that the sorting icons are one under
     * another. 'horizontal' means that the icons should be one near other.
     *
     * The third parameter determines the click functionality. If set to true the columns are sorted if the header is
     * clicked. If set to false the columns are sorted only when the icons are clicked.
     *
     * Important note: When set a third parameter to false be a sure that the first parameter is set to true, otherwise
     * you will loose the sorting.
     *
     * @var array
     */
    protected $_sortingColumnsDefinition = array(true, 'vertical', true);

    /**
     * Enables (disables) the tree grid format.
     *
     * @var bool
     */
    protected $_treeGrid = null;

    /**
     * Deteremines the method used for the treeGrid. Can be 'nested' or 'adjacency'
     *
     * @var string
     */
    protected $_treeGridType = null;

    /**
     * This array set the icons used in the tree. The icons should be a valid names from UI theme roller images.
     * The default values are:
     *
     * array(
     *  'plus' => 'ui-icon-triangle-1-e',
     * 	'minus' => 'ui-icon-triangle-1-s',
     *  'leaf' => 'ui-icon-radio-off'
     * );
     *
     * @var string
     */
    protected $_treeGridIcons = null;

    /**
     * The url of the file that holds the request
     *
     * @var string
     */
    protected $_url = null;

    /**
     * If this option is not set, the width of the grid is a sum of the widths of the columns defined (in pixels).
     * If this option is set, the initial width of each column is set according to the value of shrinkToFit option.
     *
     * @var int
     */
    protected $_width = null;

    /**
     * @param ServiceLocatorInterface $serviceManager
     */
    public function __construct(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        $this->init();
    }

    /**
     * Constructor
     *
     * @param string|array|Traversable $options
     * @return void
     */
/*	public function __construct($options = null)
    {
        if(is_string($options)) {
            $this->setName($options);
        }
        if ($options instanceof Traversable) {
            $options = IteratorToArray::convert($options);
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }

        $this->init();
    }
*/
    /**
     * Initialize grid (used by extending classes)
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set grid state from options array
     *
     * @param  array $options
     * @return Grid
     */
    public function setOptions(array $options)
    {
        if (isset($options['columns'])) {
            $this->setColumns($options['columns']);
            unset($options['columns']);
        }

        $forbidden = array(
            'Options', 'Config', 'Translator',
        );

        foreach ($options as $key => $value) {
            $normalized = ucfirst($key);
            if (in_array($normalized, $forbidden)) {
                continue;
            }

            $method = 'set' . $normalized;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Set grid state from config object
     *
     * @param  Config $config
     * @return Grid
     */
    public function setConfig(Config $config)
    {
        $this->setOptions($config->toArray());

        return $this;
    }


    /**
     * Set the column plugin manager
     *
     * @param string|ColumnPluginManager $columns
     * @throws Exception\InvalidArgumentException
     */
    public static function setColumnPluginManager($columns)
    {
        if (is_string($columns)) {
            if (!class_exists($columns)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unable to locate column plugin manager with class "%s"; class not found',
                    $columns
                ));
            }
            $columns = new $columns();
        }
        if (!$columns instanceof ColumnPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Grid column manager must extend ColumnPluginManager; received "%s"',
                (is_object($columns) ? get_class($columns) : gettype($columns))
            ));
        }
        self::$_columnPluginManager = $columns;
    }

    /**
     * Returns the column plugin manager.  If it doesn't exist it's created.
     *
     * @return ColumnPluginManager
     */
    public static function getColumnPluginManager()
    {
        if (self::$_columnPluginManager === null) {
            self::setColumnPluginManager(new ColumnPluginManager());
        }

        return self::$_columnPluginManager;
    }

    // Grid metadata:

    /**
     * Set grid adapter
     *
     * @param  \LemoBase\Grid\Adapter\AbstractAdapter $adapter
     * @return Grid
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->_adapter = $adapter;

        return $this;
    }

    /**
     * Return grid adapter.
     *
     * @return \LemoBase\Grid\Adapter\AbstractAdapter
     */
    public function getAdapter()
    {
        $this->_adapter->setGrid($this);

        return $this->_adapter;
    }

    /**
     * Get grid id
     *
     * @return string
     */
    public function getId()
    {
        $id = $this->getName();

        // Bail early if no array notation detected
        if (!strstr($id, '[')) {
            return $id;
        }

        // Strip array notation
        if ('[]' == substr($id, -2)) {
            $id = substr($id, 0, strlen($id) - 2);
        }
        $id = str_replace('][', '-', $id);
        $id = str_replace(array(']', '['), '-', $id);
        $id = trim($id, '-');

        return $id;
    }

    /**
     * Filter a name to only allow valid variable characters
     *
     * @param  string $value
     * @param  bool $allowBrackets
     * @return string
     */
    public function filterName($value, $allowBrackets = false)
    {
        $charset = '^a-zA-Z0-9_\x7f-\xff';
        if ($allowBrackets) {
            $charset .= '\[\]';
        }
        return preg_replace('/[' . $charset . ']/', '', (string) $value);
    }

    /**
     * Set grid name
     *
     * @param  string $name
     * @return Grid
     */
    public function setName($name)
    {
        $name = $this->filterName($name);
        if ('' === (string)$name) {
            throw new Exception\InvalidArgumentException('Invalid name provided; must contain only valid variable characters and be non-empty');
        }

        return $this->_name = $name;
    }

    /**
     * Get name attribute
     *
     * @return null|string
     */
    public function getName()
    {
        if (null == $this->_name) {
            $this->setName('grid');
        }

        return $this->_name;
    }

    /**
     * Set grid order
     *
     * @param  int $index
     * @return Grid
     */
    public function setOrder($index)
    {
        $this->_gridOrder = (int) $index;

        return $this;
    }

    /**
     * Get form order
     *
     * @return int|null
     */
    public function getOrder()
    {
        return $this->_gridOrder;
    }

    /**
     * Set query params
     *
     * @param array $params
     * @return \LemoBase\Grid\Grid
     */
    public function setQueryParams($params)
    {
        if(isset($params['filters'])) {
            if(is_array($params['filters'])) {
                $rules = $params['filters'];
            } else {
                $rules = \Zend\Json\Decoder::decode(stripslashes($params['filters']), \Zend\Json\Json::TYPE_ARRAY);
            }

            foreach($rules['rules'] as $rule) {
                $params[$rule['field']] = $rule['data'];
            }
        }

        $this->_queryParams = $params;

        return $this;
    }

    /**
     * Get query param
     *
     * @param string $name
     * @return string
     */
    public function getQueryParam($name)
    {
        if(null === $this->_queryParams) {
            $this->setQueryParams($this->getRequest()->getQuery()->toArray());
        }

        if(isset($this->_queryParams[$name])) {
            return $this->_queryParams[$name];
        }

        return null;
    }

    /**
     * Get query params
     *
     * @return array
     */
    public function getQueryParams()
    {
        if(null === $this->_queryParams) {
            $this->setQueryParams($this->getRequest()->getQuery()->toArray());
        }

        return $this->_queryParams;
    }

    /**
     * @param string $sessionNamespace
     * @return Grid
     */
    public function setSessionNamespace($sessionNamespace)
    {
        $this->_sessionNamespace = $sessionNamespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionNamespace()
    {
        return $this->_sessionNamespace;
    }

    /**
     * Set grid source
     *
     * @param string $source
     * @return Grid
     */
    public function setSource($source)
    {
        $this->_source = $source;

        return $this;
    }

    /**
     * Return grid source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->_source;
    }

    // Column interaction:

    /**
     * Add a new column
     *
     * $column may be either a string column type, or an object of type
     * LemoBase\Grid\Column. If a string element type is provided, $name must be
     * provided, and $options may be optionally provided for configuring the
     * element.
     *
     * If a LemoBase\Grid\Column is provided, $name may be optionally provided,
     * and any provided $options will be ignored.
     *
     * @param  string|Column $column
     * @param  string $name
     * @param  array|Traversable $options
     * @return Grid
     */
    public function addColumn($column, $name = null, $options = null)
    {
        if (is_string($column)) {
            if (null === $name) {
                throw new Exception\InvalidArgumentException('Columns specified by string must have an accompanying name');
            }

            $this->_columns[$name] = $this->createColumn($column, $name, $options);
        } elseif ($column instanceof Column) {
            if (null === $name) {
                $name = $column->getName();
            }

            $this->_columns[$name] = $column;
        }

        $this->_order[$name] = $this->_columns[$name]->getOrder();
        $this->_orderUpdated = true;

        return $this;
    }

    /**
     * Create an column
     *
     * Acts as a factory for creating columns. Columns created with this
     * method will not be attached to the grid, but will contain column
     * settings as specified in the grid object (including plugin loader
     * prefix paths, etc.).
     *
     * @param  string $type
     * @param  string $name
     * @param  array|Traversable $options
     * @return Column
     */
    public function createColumn($type, $name, $options = null)
    {
        if (!is_string($type)) {
            throw new Exception\InvalidArgumentException('Column type must be a string indicating type');
        }

        if (!is_string($name)) {
            throw new Exception\InvalidArgumentException('Column name must be a string');
        }

        if ($options instanceof Traversable) {
            $options = IteratorToArray::convert($options);
        }

        $options['name'] = $name;

        $column = $this->getColumnPluginManager()->get($type, $options);
        $column->setGrid($this);

        return $column;
    }

    /**
     * Add multiple columns at once
     *
     * @param  array $columns
     * @return Grid
     */
    public function addColumns(array $columns)
    {
        foreach ($columns as $key => $spec) {
            $name = null;
            if (!is_numeric($key)) {
                $name = $key;
            }

            if (is_string($spec) || ($spec instanceof Column)) {
                $this->addColumn($spec, $name);
                continue;
            }

            if (is_array($spec)) {
                $argc = count($spec);
                $options = array();
                if (isset($spec['type'])) {
                    $type = $spec['type'];
                    if (isset($spec['name'])) {
                        $name = $spec['name'];
                    }
                    if (isset($spec['options'])) {
                        $options = $spec['options'];
                    }
                    $this->addColumn($type, $name, $options);
                } else {
                    switch ($argc) {
                        case 0:
                            continue;
                        case (1 <= $argc):
                            $type = array_shift($spec);
                        case (2 <= $argc):
                            if (null === $name) {
                                $name = array_shift($spec);
                            } else {
                                $options = array_shift($spec);
                            }
                        case (3 <= $argc):
                            if (empty($options)) {
                                $options = array_shift($spec);
                            }
                        default:
                            $this->addColumn($type, $name, $options);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Set grid columns (overwrites existing columns)
     *
     * @param  array $columns
     * @return Grid
     */
    public function setColumns(array $columns)
    {
        $this->clearColumns();
        return $this->addColumns($columns);
    }

    /**
     * Retrieve a single column
     *
     * @param  string $name
     * @return Column|null
     */
    public function getColumn($name)
    {
        if (isset($this->_columns[$name])) {
            return $this->_columns[$name];
        }
        return null;
    }

    /**
     * Retrieve all columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * Remove column
     *
     * @param  string $name
     * @return boolean
     */
    public function removeColumn($name)
    {
        $name = (string) $name;
        if (isset($this->_columns[$name])) {
            unset($this->_columns[$name]);
            if (array_key_exists($name, $this->_order)) {
                unset($this->_order[$name]);
                $this->_orderUpdated = true;
            }
            return true;
        }

        return false;
    }

    /**
     * Remove all grid columns
     *
     * @return Grid
     */
    public function clearColumns()
    {
        foreach (array_keys($this->_columns) as $key) {
            if (array_key_exists($key, $this->_order)) {
                unset($this->_order[$key]);
            }
        }

        $this->_columns     = array();
        $this->_orderUpdated = true;

        return $this;
    }

    /**
     * Has grid column?
     *
     * @return bool
     */
    public function hasColumn($name)
    {
        if(isset($this->_columns[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Set request instance
     *
     * @param \Zend\Http\PhpEnvironment\Request $request
     * @return Grid
     */
    public function setRequest(\Zend\Http\PhpEnvironment\Request $request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     * Return instance of request
     *
     * @return \Zend\Http\PhpEnvironment\Request
     */
    public function getRequest()
    {
        if(null === $this->_request) {
            $this->_request = $this->getServiceManager()->get('Zend\Http\PhpEnvironment\Request');
        }

        return $this->_request;
    }

    // Rendering

    /**
     * Retrieves the view instance.
     *
     * If none registered, instantiates a PhpRenderer instance.
     *
     * @return \Zend\View\Renderer\RendererInterface|null
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = $this->getServiceManager()->get('Zend\View\Renderer\PhpRenderer');
        }

        return $this->_view;
    }

    /**
     * Sets the view object.
     *
     * @param  \Zend\View\Renderer\RendererInterface $view
     * @return Paginator
     */
    public function setView(View\Renderer\RendererInterface $view = null)
    {
        $this->_view = $view;

        return $this;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $locator
     * @return void
     */
    public function setServiceManager(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function render()
    {
        $sessionContainer = new \Zend\Session\Container($this->getSessionNamespace());
        $query = $this->getQueryParams();

        $sessionName = 'grid_' . $this->getName();

        if($this->getRequest()->isXmlHttpRequest() && $this->getQueryParam('_name') != $this->getName()) {
            return null;
        }

        if($sessionContainer->offsetExists($sessionName)) {
            $session = $sessionContainer->offsetGet($sessionName);
        } else {
            $session = $sessionContainer->offsetSet($sessionName, array());
        }

        if(true === $this->getRequest()->isXmlHttpRequest()) {
            if(isset($session['loaded']) && $session['loaded'] == true) {
                if(isset($session['filters']) && $session['loaded'] == true && !isset($query['filters'])) {
                    $query['filters'] = $session['filters'];
                }

                $session['page'] = @$query['page'];
                $session['rows'] = @$query['rows'];
                $session['sidx'] = @$query['sidx'];
                $session['sord'] = @$query['sord'];
                $session['filters'] = @$query['filters'];

            } else {
                $session['loaded'] = true;

                if(isset($session['page'])) {
                    $query['page'] = $session['page'];
                    $query['rows'] = $session['rows'];
                    $query['sidx'] = $session['sidx'];
                    $query['sord'] = $session['sord'];
                    $query['filters'] = $session['filters'];
                }
            }
        } else {
            unset($session['loaded']);

            if(isset($session['page'])) {
                $query['page'] = $session['page'];
                $query['rows'] = $session['rows'];
                $query['sidx'] = $session['sidx'];
                $query['sord'] = $session['sord'];
                $query['filters'] = $session['filters'];
            }
        }

        $sessionContainer->offsetSet($sessionName, $session);
        $this->setQueryParams($query);

    // ===== RENDERING =====

        if(true == $this->getRequest()->isXmlHttpRequest()) {
            $this->getAdapter()->setGrid($this);
            $items = array();
            $data = $this->getAdapter()->getData();

            if(is_array($data)) {
                foreach($data as $index => $item) {
                    $rowData = $item;

                    if($this->getTreeGrid() == true AND $this->getTreeGridModel() == self::TREE_MODEL_NESTED) {
                        $item['leaf'] = ($item['rgt'] == $item['lft'] + 1) ? 'true' : 'false';
                        $item['expanded'] = 'true';
                    }

                    if($this->getTreeGrid() == true AND $this->getTreeGridModel() == self::TREE_MODEL_ADJACENCY) {
                        $item['parent'] = $item['level'] > 0 ? $item['parent'] : 'NULL';
                        $item['leaf'] = $item['child_count'] > 0 ? 'false' : 'true';
                        $item['expanded'] = 'true';
                    }

                    // Pridame radek
                    $items[] = array(
                        'id' => $index +1,
                        'cell' => array_values($rowData)
                    );
                }
            }

            @ob_clean();
            echo \Zend\Json\Encoder::encode(array(
                'page' => $this->getAdapter()->getNumberOfCurrentPage(),
                'total' => $this->getAdapter()->getNumberOfPages(),
                'records' => $this->getAdapter()->getNumberOfRecords(),
                'rows' => $items,
            ));
            exit;
        } else {
            $colNames = array();

            foreach($this->_gridOptions as $nameProperty => $nameGrid) {
                $methodName = 'get' . ucfirst($nameProperty);

                if(method_exists($this, $methodName)) {
                    $value = call_user_func(array($this, $methodName));

                    if(null === $value) {
                        if('pagerElementId'== $nameProperty) {
                            $value = $this->getId() . '_pager';
                        }
                        if('height' == $nameProperty) {
                            $value = '100%';
                        }
                    }

                    if(!empty($value) || is_bool($value)) {
                        if(null !== $nameGrid) {
                            $nameProperty = $nameGrid;
                        }

                        $attribs[strtolower($nameProperty)] = $value;
                    }
                }
            }

            if(isset($query['sidx'])) {
                $attribs['sortname'] = $query['sidx'];
            } else {
                $attribs['sortname'] = $this->getDefaultSortColumn();
            }
            if(isset($query['sord'])) {
                $attribs['sortorder'] = $query['sord'];
            } else {
                $attribs['sortorder'] = $this->getDefaultSortOrder();
            }

            ksort($attribs);

            foreach($this->getColumns() as $column) {
                $label = $column->getLabel();

                if(!empty($label)) {
                    $label = $this->getView()->translate($label);

                }

                $colNames[] = $label;

                unset($label);
            }

            $script[] = '	$(document).ready(function(){';
            $script[] = '		$(\'#' . $this->getId() . '\').jqGrid({';

            foreach($attribs as $key => $value) {
                if(is_array($value)) {
                    $values = array();
                    foreach($value as $k => $val) {
                        if(is_bool($val)) {
                            if($val == true) {
                                $values[] = 'true';
                            } else {
                                $values[] = 'false';
                            }
                        } elseif(is_numeric($val)) {
                            $values[] = $val;
                        } elseif(strtolower($key) == 'treeicons') {
                            $values[] = $k . ":'" .  $val . "'";
                        } else {
                            $values[] = "'" .  $val . "'";
                        }
                    }

                    if(strtolower($key) == 'treeicons') {
                        $script[] = '			' . $key . ': {' . implode(',', $values) . '},';
                    } else {
                        $script[] = '			' . $key . ': [' . implode(',', $values) . '],';
                    }
                } elseif(is_numeric($value)) {
                    $script[] = '			' . $key . ': ' . $value . ',';
                } elseif(is_bool($value)) {
                    if($value == true) {
                        $value = 'true';
                    } else {
                        $value = 'false';
                    }
                    $script[] = '			' . $key . ': ' . $value . ',';
                } else {
                    $script[] = '			' . $key . ': \'' . $value . '\',';
                }
            }

            $script[] = '			colNames: [\'' . implode('\', \'', $colNames) . '\'],';
            $script[] = '			colModel: [';

            $columnsCount = count($this->getColumns());
            $a = 1;
            foreach($this->getColumns() as $column) {
                if($a != $columnsCount) { $delimiter = ','; } else { $delimiter = ''; }
                $script[] = '				{' . $column->render() . '}' . $delimiter;
                $a++;
            }

            $script[] = '			]';
            $script[] = '		});';

            $filterToolbar = $this->getFilterToolbar();
            if($filterToolbar['enabled'] == true) {
                $filterToolbar = $this->getFilterToolbar();
                if($filterToolbar['stringResult'] == true) { $stringResult = 'true'; } else { $stringResult = 'false'; }
                if($filterToolbar['searchOnEnter'] == true) { $searchOnEnter = 'true'; } else { $searchOnEnter = 'false'; }
                $script[] = '		$(\'#' . $this->getName() . '\').jqGrid(\'filterToolbar\',{stringResult: ' . $stringResult . ', searchOnEnter: ' . $searchOnEnter . '});' . PHP_EOL;
            }

            $script[] = '	$(window).bind(\'resize\', function() {';
            $script[] = '		$(\'#' . $this->getId() . '\').setGridWidth($(\'#gbox_' . $this->getId() . '\').parent().width());';
            $script[] = '	}).trigger(\'resize\');';

            $script[] = '	});';
            $xhtml[] = '<table id="' . $this->getId() . '"></table>';

            // Pokud se nema zobrazit paticka
            if($this->getRenderFooterRow() !== false) {
                $xhtml[] = '<div id="' . $attribs['pager'] . '"></div>';
            }
        }

        $this->getView()->inlineScript()->appendScript(implode(PHP_EOL, $script));

        return implode(PHP_EOL, $xhtml);
    }

    /**
     * Serializes the object as a string.  Proxies to {@link render()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {

            ob_clean();
            trigger_error($e->getMessage(), E_USER_WARNING);

            return $e->getMessage();
        }

        return '';
    }

    // ==== GRID PROPERTIES SETTERS / GETTERS ====


    /**
     * @param boolean $alternativeRows
     * @return \LemoBase\Grid\Grid
     */
    public function setAlternativeRows($alternativeRows)
    {
        $this->_alternativeRows = $alternativeRows;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAlternativeRows()
    {
        return $this->_alternativeRows;
    }

    /**
     * @param string $alternativeRowsClass
     * @return \LemoBase\Grid\Grid
     */
    public function setAlternativeRowsClass($alternativeRowsClass)
    {
        $this->_alternativeRowsClass = $alternativeRowsClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlternativeRowsClass()
    {
        return $this->_alternativeRowsClass;
    }

    /**
     * @param boolean $autoEncodeIncomingAndPostData
     * @return \LemoBase\Grid\Grid
     */
    public function setAutoEncodeIncomingAndPostData($autoEncodeIncomingAndPostData)
    {
        $this->_autoEncodeIncomingAndPostData = $autoEncodeIncomingAndPostData;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAutoEncodeIncomingAndPostData()
    {
        return $this->_autoEncodeIncomingAndPostData;
    }

    /**
     * @param boolean $autowidth
     * @return \LemoBase\Grid\Grid
     */
    public function setAutowidth($autowidth)
    {
        $this->_autowidth = $autowidth;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAutowidth()
    {
        return $this->_autowidth;
    }

    /**
     * @param string $caption
     * @return \LemoBase\Grid\Grid
     */
    public function setCaption($caption)
    {
        $this->_caption = $caption;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->_caption;
    }

    /**
     * @param boolean $cellEdit
     * @return \LemoBase\Grid\Grid
     */
    public function setCellEdit($cellEdit)
    {
        $this->_cellEdit = $cellEdit;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getCellEdit()
    {
        return $this->_cellEdit;
    }

    /**
     * @param string $cellEditUrl
     * @return \LemoBase\Grid\Grid
     */
    public function setCellEditUrl($cellEditUrl)
    {
        $this->_cellEditUrl = $cellEditUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getCellEditUrl()
    {
        return $this->_cellEditUrl;
    }

    /**
     * @param int $cellLayout
     * @return \LemoBase\Grid\Grid
     */
    public function setCellLayout($cellLayout)
    {
        $this->_cellLayout = $cellLayout;

        return $this;
    }

    /**
     * @return int
     */
    public function getCellLayout()
    {
        return $this->_cellLayout;
    }

    /**
     * @param string $cellSaveType
     * @return \LemoBase\Grid\Grid
     */
    public function setCellSaveType($cellSaveType)
    {
        $this->_cellSaveType = $cellSaveType;

        return $this;
    }

    /**
     * @return string
     */
    public function getCellSaveType()
    {
        return $this->_cellSaveType;
    }

    /**
     * @param string $cellSaveUrl
     * @return \LemoBase\Grid\Grid
     */
    public function setCellSaveUrl($cellSaveUrl)
    {
        $this->_cellSaveUrl = $cellSaveUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getCellSaveUrl()
    {
        return $this->_cellSaveUrl;
    }

    /**
     * @param array $data
     * @return \LemoBase\Grid\Grid
     */
    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param string $dataString
     * @return \LemoBase\Grid\Grid
     */
    public function setDataString($dataString)
    {
        $this->_dataString = $dataString;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataString()
    {
        return $this->_dataString;
    }

    /**
     * @param string $dataType
     * @return \LemoBase\Grid\Grid
     */
    public function setDataType($dataType)
    {
        $normalized = strtolower($dataType);

        if(!in_array($normalized, array('xml', 'xmlstring', 'json', 'jsonstring', 'local', 'javascript'))) {
            throw new Exception\InvalidArgumentException("DataType must be 'xml', 'xmlstring', 'json', 'jsonstring', 'local' or 'javascript'");
        }

        $this->_dataType = $dataType;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->_dataType;
    }

    /**
     * @param int $defaultPage
     * @return \LemoBase\Grid\Grid
     */
    public function setDefaultPage($defaultPage)
    {
        $this->_defaultPage = $defaultPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultPage()
    {
        return $this->_defaultPage;
    }

    /**
     * @param string $defaultSortColumn
     * @return \LemoBase\Grid\Grid
     */
    public function setDefaultSortColumn($defaultSortColumn)
    {
        $this->_defaultSortColumn = $defaultSortColumn;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultSortColumn($fromFirstColumn = true)
    {
        if(null === $this->_defaultSortColumn) {
            if(true === $fromFirstColumn) {
                $col = current($this->getColumns());

                if($col) {
                    return $col->getName();
                } else {
                    throw new Exception\InvalidArgumentException('Default sort column was not defined');
                }
            }
        }

        return $this->_defaultSortColumn;
    }

    /**
     * @param string $defaultSortOrder
     * @return \LemoBase\Grid\Grid
     */
    public function setDefaultSortOrder($defaultSortOrder)
    {
        $this->_defaultSortOrder = strtolower($defaultSortOrder);

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultSortOrder()
    {
        return $this->_defaultSortOrder;
    }

    /**
     * @param string $expandColumnIdentifier
     * @return \LemoBase\Grid\Grid
     */
    public function setExpandColumnIdentifier($expandColumnIdentifier)
    {
        $this->_expandColumnIdentifier = $expandColumnIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpandColumnIdentifier()
    {
        return $this->_expandColumnIdentifier;
    }

    /**
     * @param boolean $expandColumnOnClick
     * @return \LemoBase\Grid\Grid
     */
    public function setExpandColumnOnClick($expandColumnOnClick)
    {
        $this->_expandColumnOnClick = $expandColumnOnClick;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getExpandColumnOnClick()
    {
        return $this->_expandColumnOnClick;
    }

    public function setFilterToolbar($enabled = true, $searchOnEnter = null)
    {
        $this->_filterToolbar = array(
            'enabled' => $enabled,
            'stringResult' => true,
            'searchOnEnter' => $searchOnEnter,
        );

        return $this;
    }

    public function getFilterToolbar()
    {
        return $this->_filterToolbar;
    }

    /**
     * @param boolean $forceFit
     * @return \LemoBase\Grid\Grid
     */
    public function setForceFit($forceFit)
    {
        $this->_forceFit = $forceFit;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getForceFit()
    {
        return $this->_forceFit;
    }

    /**
     * @param string $gridState
     * @return \LemoBase\Grid\Grid
     */
    public function setGridState($gridState)
    {
        $this->_gridState = $gridState;

        return $this;
    }

    /**
     * @return string
     */
    public function getGridState()
    {
        return $this->_gridState;
    }

    /**
     * @param boolean $grouping
     * @return \LemoBase\Grid\Grid
     */
    public function setGrouping($grouping)
    {
        $this->_grouping = $grouping;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getGrouping()
    {
        return $this->_grouping;
    }

    /**
     * @param string $headerTitles
     * @return \LemoBase\Grid\Grid
     */
    public function setHeaderTitles($headerTitles)
    {
        $this->_headerTitles = $headerTitles;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeaderTitles()
    {
        return $this->_headerTitles;
    }

    /**
     * @param string $height
     * @return \LemoBase\Grid\Grid
     */
    public function setHeight($height)
    {
        $this->_height = $height;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * @param boolean $hoverRows
     * @return \LemoBase\Grid\Grid
     */
    public function setHoverRows($hoverRows)
    {
        $this->_hoverRows = $hoverRows;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getHoverRows()
    {
        return $this->_hoverRows;
    }

    /**
     * @param boolean $loadOnce
     * @return \LemoBase\Grid\Grid
     */
    public function setLoadOnce($loadOnce)
    {
        $this->_loadOnce = $loadOnce;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getLoadOnce()
    {
        return $this->_loadOnce;
    }

    /**
     * @param string $loadType
     * @return \LemoBase\Grid\Grid
     */
    public function setLoadType($loadType)
    {
        $this->_loadType = $loadType;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoadType()
    {
        return $this->_loadType;
    }

    /**
     * @param boolean $multiSelect
     * @return \LemoBase\Grid\Grid
     */
    public function setMultiSelect($multiSelect)
    {
        $this->_multiSelect = $multiSelect;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMultiSelect()
    {
        return $this->_multiSelect;
    }

    /**
     * @param string $multiSelectKey
     * @return \LemoBase\Grid\Grid
     */
    public function setMultiSelectKey($multiSelectKey)
    {
        $this->_multiSelectKey = $multiSelectKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getMultiSelectKey()
    {
        return $this->_multiSelectKey;
    }

    /**
     * @param int $multiSelectWidth
     * @return \LemoBase\Grid\Grid
     */
    public function setMultiSelectWidth($multiSelectWidth)
    {
        $this->_multiSelectWidth = $multiSelectWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getMultiSelectWidth()
    {
        return $this->_multiSelectWidth;
    }

    /**
     * @param string $pagerElementId
     * @return Grid
     */
    public function setPagerElementId($pagerElementId)
    {
        $this->_pagerElementId = $pagerElementId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerElementId()
    {
        return $this->_pagerElementId;
    }

    /**
     * @param string $pagerPosition
     * @return \LemoBase\Grid\Grid
     */
    public function setPagerPosition($pagerPosition)
    {
        $this->_pagerPosition = $pagerPosition;

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerPosition()
    {
        return $this->_pagerPosition;
    }

    /**
     * @param boolean $pagerShowButtons
     * @return \LemoBase\Grid\Grid
     */
    public function setPagerShowButtons($pagerShowButtons)
    {
        $this->_pagerShowButtons = $pagerShowButtons;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPagerShowButtons()
    {
        return $this->_pagerShowButtons;
    }

    /**
     * @param boolean $pagerShowInput
     * @return \LemoBase\Grid\Grid
     */
    public function setPagerShowInput($pagerShowInput)
    {
        $this->_pagerShowInput = $pagerShowInput;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPagerShowInput()
    {
        return $this->_pagerShowInput;
    }

    /**
     * @param string $recordPosition
     * @return \LemoBase\Grid\Grid
     */
    public function setRecordPosition($recordPosition)
    {
        $this->_recordPosition = $recordPosition;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecordPosition()
    {
        return $this->_recordPosition;
    }

    /**
     * @param int $recordsPerPage
     * @return \LemoBase\Grid\Grid
     */
    public function setRecordsPerPage($recordsPerPage)
    {
        $this->_recordsPerPage = $recordsPerPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getRecordsPerPage()
    {
        $rows = $this->getQueryParam('rows');

        if(null !== $rows) {
            $this->_recordsPerPage = $rows;
        }

        return $this->_recordsPerPage;
    }

    /**
     * @param array $recordsPerPageList
     * @return \LemoBase\Grid\Grid
     */
    public function setRecordsPerPageList($recordsPerPageList)
    {
        $this->_recordsPerPageList = $recordsPerPageList;

        return $this;
    }

    /**
     * @return array
     */
    public function getRecordsPerPageList()
    {
        return $this->_recordsPerPageList;
    }

    /**
     * @param boolean $renderFooterRow
     * @return \LemoBase\Grid\Grid
     */
    public function setRenderFooterRow($renderFooterRow)
    {
        $this->_renderFooterRow = $renderFooterRow;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRenderFooterRow()
    {
        return $this->_renderFooterRow;
    }

    /**
     * @param boolean $renderHideGridButton
     * @return \LemoBase\Grid\Grid
     */
    public function setRenderHideGridButton($renderHideGridButton)
    {
        $this->_renderHideGridButton = $renderHideGridButton;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRenderHideGridButton()
    {
        return $this->_renderHideGridButton;
    }

    /**
     * @param boolean $renderRecordsInfo
     * @return \LemoBase\Grid\Grid
     */
    public function setRenderRecordsInfo($renderRecordsInfo)
    {
        $this->_renderRecordsInfo = $renderRecordsInfo;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRenderRecordsInfo()
    {
        return $this->_renderRecordsInfo;
    }

    /**
     * @param boolean $renderRowNumbersColumn
     * @return \LemoBase\Grid\Grid
     */
    public function setRenderRowNumbersColumn($renderRowNumbersColumn)
    {
        $this->_renderRowNumbersColumn = $renderRowNumbersColumn;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRenderRowNumbersColumn()
    {
        return $this->_renderRowNumbersColumn;
    }

    /**
     * @param string $requestType
     * @return \LemoBase\Grid\Grid
     */
    public function setRequestType($requestType)
    {
        $this->_requestType = $requestType;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->_requestType;
    }

    /**
     * @param string $resizeClass
     * @return \LemoBase\Grid\Grid
     */
    public function setResizeClass($resizeClass)
    {
        $this->_resizeClass = $resizeClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getResizeClass()
    {
        return $this->_resizeClass;
    }

    /**
     * @param bool|int $scroll
     * @return \LemoBase\Grid\Grid
     */
    public function setScroll($scroll)
    {
        $this->_scroll = $scroll;

        return $this;
    }

    /**
     * @return bool|int
     */
    public function getScroll()
    {
        return $this->_scroll;
    }

    /**
     * @param int $scrollOffset
     * @return \LemoBase\Grid\Grid
     */
    public function setScrollOffset($scrollOffset)
    {
        $this->_scrollOffset = $scrollOffset;

        return $this;
    }

    /**
     * @return int
     */
    public function getScrollOffset()
    {
        return $this->_scrollOffset;
    }

    /**
     * @param boolean $scrollRows
     * @return \LemoBase\Grid\Grid
     */
    public function setScrollRows($scrollRows)
    {
        $this->_scrollRows = $scrollRows;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getScrollRows()
    {
        return $this->_scrollRows;
    }

    /**
     * @param int $scrollTimeout
     * @return \LemoBase\Grid\Grid
     */
    public function setScrollTimeout($scrollTimeout)
    {
        $this->_scrollTimeout = $scrollTimeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getScrollTimeout()
    {
        return $this->_scrollTimeout;
    }

    /**
     * @param boolean $shrinkToFit
     * @return \LemoBase\Grid\Grid
     */
    public function setShrinkToFit($shrinkToFit)
    {
        $this->_shrinkToFit = $shrinkToFit;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getShrinkToFit()
    {
        return $this->_shrinkToFit;
    }

    /**
     * @param boolean $sortingColumns
     * @return \LemoBase\Grid\Grid
     */
    public function setSortingColumns($sortingColumns)
    {
        $this->_sortingColumns = $sortingColumns;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSortingColumns()
    {
        return $this->_sortingColumns;
    }

    /**
     * @param array $sortingColumnsDefinition
     * @return \LemoBase\Grid\Grid
     */
    public function setSortingColumnsDefinition($sortingColumnsDefinition)
    {
        $this->_sortingColumnsDefinition = $sortingColumnsDefinition;

        return $this;
    }

    /**
     * @return array
     */
    public function getSortingColumnsDefinition()
    {
        return $this->_sortingColumnsDefinition;
    }

    /**
     * @param boolean $treeGrid
     * @return \LemoBase\Grid\Grid
     */
    public function setTreeGrid($treeGrid)
    {
        $this->_treeGrid = $treeGrid;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getTreeGrid()
    {
        return $this->_treeGrid;
    }

    /**
     * Default:
     *  - plus: ui-icon-triangle-1-e
     *  - minus: ui-icon-triangle-1-s
     *  - leaf: ui-icon-radio-off
     *
     * @param string $plus
     * @param string $minus
     * @param string $leaf
     * @return \LemoBase\Grid\Grid
     */
    public function setTreeGridIcons($plus, $minus, $leaf)
    {
        $this->_treeGridIcons = array(
            'plus' => $plus,
            'minus' => $minus,
            'leaf' => $leaf
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getTreeGridIcons()
    {
        return $this->_treeGridIcons;
    }

    /**
     * @param string $treeGridType
     * @return \LemoBase\Grid\Grid
     */
    public function setTreeGridType($treeGridType)
    {
        $this->_treeGridType = $treeGridType;

        return $this;
    }

    /**
     * @return string
     */
    public function getTreeGridType()
    {
        return $this->_treeGridType;
    }

    /**
     * @param string $url
     * @return \LemoBase\Grid\Grid
     */
    public function setUrl($url)
    {
        $this->_url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if($this->_url == null) {
            $this->_url = 'http://' . $_SERVER['HTTP_HOST'] . $this->getRequest()->getUri()->getPath();
        }

        return $this->_url . '?_name=' . $this->getName();
    }

    /**
     * @param int $width
     * @return \LemoBase\Grid\Grid
     */
    public function setWidth($width)
    {
        $this->_width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }
}
