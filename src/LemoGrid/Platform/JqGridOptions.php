<?php

namespace LemoGrid\Platform;

use LemoGrid\Exception;
use Zend\Stdlib\AbstractOptions;

class JqGridOptions extends AbstractOptions
{
    /**
     * Data types
     */
    const DATATYPE_JAVASCRIPT = 'javascript';
    const DATATYPE_JSON       = 'json';
    const DATATYPE_JSONSTRING = 'jsonstring';
    const DATATYPE_LOCAL      = 'local';
    const DATATYPE_XML        = 'xml';
    const DATATYPE_XMLSTRING  = 'xmlstring';

    /**
     * Request types
     */
    const REQUEST_TYPE_GET  = 'get';
    const REQUEST_TYPE_POST = 'post';

    /**
     * Sort orders
     */
    const SORT_ORDER_ASC  = 'asc';
    const SORT_ORDER_DESC = 'desc';

    /**
     * Set a zebra-striped grid.
     *
     * @var bool
     */
    protected $alternativeRows;

    /**
     * The class that is used for alternate (zebra) rows. You can construct your own class and replace this value.
     * This option is valid only if altRows options is set to true.
     *
     * @var string
     */
    protected $alternativeRowsClass;

    /**
     * When set to true encodes (html encode) the incoming (from server) and posted data (from editing modules).
     * By example < will be converted to &lt;
     *
     * @var bool
     */
    protected $autoEncodeIncomingAndPostData;

    /**
     * When set to true, the grid width is recalculated automatically to the width of the parent element. This is done
     * only initially when the grid is created. In order to resize the grid when the parent element changes width you
     * should apply custom code and use the setGridWidth method for this purpose.
     *
     * @var bool
     */
    protected $autowidth;

    /**
     * Defines the Caption layer for the grid. This caption appears above the Header layer. If the string is empty
     * the caption does not appear.
     *
     * @var string
     */
    protected $caption;

    /**
     * This option determines the padding + border width of the cell. Usually this should not be changed, but if custom
     * changes to td element are made in the grid css file this will need to be changed. The initial value of 5 means
     * paddingLef?2+paddingRight?2+borderLeft?1=5.
     *
     * @var int
     */
    protected $cellLayout;

    /**
     * Enables (disables) cell editing. See Cell Editing for more details.
     *
     * @var bool
     */
    protected $cellEdit;

    /**
     * Defines the url for inline and form editing.
     *
     * @var string
     */
    protected $cellEditUrl;

    /**
     * Determines where the contents of the cell are saved: 'remote' or 'clientArray'.
     *
     * @var string
     */
    protected $cellSaveType;

    /**
     * The url where the cell is to be saved.
     *
     * @var string
     */
    protected $cellSaveUrl;

    /**
     * A array that store the local data passed to the grid. You can directly point to this variable in case you want
     * to load a array data. It can replace addRowData method which is slow on relative big data.
     *
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $dataString;

    /**
     * Defines what type of information to expect to represent data in the grid. Valid options are xml - we expect
     * xml data; xmlstring - we expect xml data as string; json - we expect JSON data; jsonstring - we expect JSON data
     * as string; local - we expect data defined at client side (array data); javascript - we expect javascript as data;
     * function - custom defined function for retrieving data.
     *
     * @var string
     */
    protected $dataType = self::DATATYPE_JSON;

    /**
     * Enables grouping in grid.
     *
     * @var bool
     */
    protected $grouping;

    /**
     * @var array
     */
    protected $groupingView;

    /**
     * Indicates which column should be used to expand the tree grid. If not set the first one
     * is used. Valid only when treeGrid option is set to true.
     *
     * @var string
     */
    protected $expandColumnIdentifier;

    /**
     * When true, the treeGrid is expanded and/or collapsed when we click on the text of the expanded column, not
     * only on the image
     *
     * @var bool
     */
    protected $expandColumnOnClick;

    /**
     * @var array
     */
    protected $filterToolbar = array(
        'stringResult' => true,
        'searchOnEnter' => true,
        'searchOperators' => false,
    );

    /**
     * Is filter toolbat enabled?
     *
     * @var bool
     */
    protected $filterToolbarEnabled = true;

    /**
     * If set to true, and resizing the width of a column, the adjacent column (to the right) will resize so that
     * the overall grid width is maintained (e.g., reducing the width of column 2 by 30px will increase the size of
     * column 3 by 30px). In this case there is no horizontal scrolbar. Note: this option is not compatible with
     * shrinkToFit option - i.e if shrinkToFit is set to false, forceFit is ignored.
     *
     * @var bool
     */
    protected $forceFit = true;

    /**
     * Determines the current state of the grid (i.e. when used with hiddengrid, hidegrid and caption options). Can
     * have either of two states: 'visible' or 'hidden'
     *
     * @var string
     */
    protected $gridState;

    /**
     * In the previous versions of jqGrid including 3.4.X, reading a relatively large data set (number of rows >= 100)
     * caused speed problems. The reason for this was that as every cell was inserted into the grid we applied about
     * 5 to 6 jQuery calls to it. Now this problem is resolved; we now insert the entry row at once with a jQuery
     * append. The result is impressive - about 3 to 5 times faster. What will be the result if we insert all the data
     * at once? Yes, this can be done with a help of gridview option (set it to true). The result is a grid that is
     * 5 to 10 times faster. Of course, when this option is set to true we have some limitations. If set to true we can
     * not use treeGrid, subGrid, or the afterInsertRow event. If you do not use these three options in the grid you can
     * set this option to true and enjoy the speed.
     *
     * @var bool
     */
    protected $gridView = true;

    /**
     * If the option is set to true the title attribute is added to the column headers.
     *
     * @var string
     */
    protected $headerTitles;

    /**
     * The height of the grid. Can be set as number (in this case we mean pixels) or as percentage
     * (only 100% is acceped) or value of auto is acceptable.
     *
     * @var string
     */
    protected $height = '100%';

    /**
     * When set to false the effect of mouse hovering over the grid data rows is disabled.
     *
     * @var bool
     */
    protected $hoverRows;

    /**
     * If this flag is set to true, the grid loads the data from the server only once (using the appropriate datatype).
     * After the first request the datatype parameter is automatically changed to local and all further manipulations
     * are done on the client side. The functions of the pager (if present) are disabled.
     *
     * @var bool
     */
    protected $loadOnce;

    /**
     * This option controls what to do when an ajax operation is in progress.
     * 'disable', 'enable' or 'block'
     *
     * @var string
     */
    protected $loadType;

    /**
     * If this flag is set to true a multi selection of rows is enabled. A new column at left side is added. Can be used
     * with any datatype option.
     *
     * @var bool
     */
    protected $multiSelect;

    /**
     * This parameter have sense only multiselect option is set to true. Defines the key which will be pressed when we
     * make multiselection. The possible values are: shiftKey - the user should press Shift Key altKey - the user should
     * press Alt Key ctrlKey - the user should press Ctrl Key
     *
     * 'shiftKey', 'altKey', 'ctrlKey'
     *
     * @var string
     */
    protected $multiSelectKey;

    /**
     * Determines the width of the multiselect column if multiselect is set to true.
     *
     * @var int
     */
    protected $multiSelectWidth;

    /**
     * If set to true enables the multisorting. The options work if the datatype is local. In case when the data is
     * obtained from the server the sidx parameter contain the order clause. It is a comma separated string in format
     * field1 asc, field2 desc …, fieldN. Note that the last field does not not have asc or desc. It should be obtained
     * from sord parameter. When the option is true the behavior is a s follow. The first click of the header field sort
     * the field depending on the firstsortoption parameter in colModel or sortorder grid parameter. The next click sort
     * it in reverse order. The third click removes the sorting from this field
     *
     * @var bool
     */
    protected $multiSort = true;

    /**
     * The initial page number when we make the request.This parameter is passed to the url for use by the server
     * routine retrieving the data.
     *
     * @var int
     */
    protected $page = 1;

    /**
     * Defines that we want to use a pager bar to navigate through the records. This must be a valid html element;
     * in our example we gave the div the id of “pager”, but any name is acceptable. Note that the Navigation layer
     * (the “pager” div) can be positioned anywhere you want, determined by your html; in our example we specified that
     * the pager will appear after the Table Body layer.
     *
     * @var string
     */
    protected $pagerElementId;

    /**
     * Determines the position of the pager in the grid. By default the pager element when created is divided in 3 parts
     * (one part for pager, one part for navigator buttons and one part for record information)
     *
     * 'left', 'center' or 'right'
     *
     * @var string
     */
    protected $pagerPosition;

    /**
     * Determines if the Pager buttons should be shown if pager is available. Also valid only if pager is set correctly.
     * The buttons are placed in the pager bar.
     *
     * @var bool
     */
    protected $pagerShowButtons;

    /**
     * Determines if the input box, where the user can change the number of requested page, should be available.
     * The input box appear in the pager bar.
     *
     * @var bool
     */
    protected $pagerShowInput;

    /**
     * Determines the position of the record information in the pager.
     *
     * 'left', 'center' or 'right'
     *
     * @var string
     */
    protected $recordPosition;

    /**
     * Defines the type of request to make ('post' or 'get')
     *
     * @var string
     */
    protected $requestType = self::REQUEST_TYPE_GET;

    /**
     * If set to true this will place a footer table with one row below the gird records and above the pager.
     *
     * @var bool
     */
    protected $renderFooterRow;

    /**
     * Enables or disables the show/hide grid button, which appears on the right side of the Caption layer. Takes effect
     * only if the caption property is not an empty string.
     *
     * @var bool
     */
    protected $renderHideGridButton;

    /**
     * If this option is set to true, a new column at left of the grid is added. The purpose of this column is to count
     * the number of available rows, beginning from 1. In this case colModel is extended automatically with new element
     * with name - 'rn'. Also, be careful not to use the name 'rn'.
     *
     * @var bool
     */
    protected $renderRowNumbersColumn;

    /**
     * If true, jqGrid displays the beginning and ending record number in the grid, out of the total number of records
     * in the query. This information is shown in the pager bar (bottom right by default)in this format:
     * “View X to Y out of Z”. If this value is true, there are other parameters that can be adjusted,
     * including 'emptyrecords' and 'recordtext'.
     *
     * @var bool
     */
    protected $renderRecordsInfo = true;

    /**
     * Assigns a class to columns that are resizable so that we can show a resize handle only for ones that are
     * resizable.
     *
     * @var string
     */
    protected $resizeClass;

    /**
     * Sets how many records we want to view in the grid. This parameter is passed to the url for use by the server
     * routine retrieving the data. Note that if you set this parameter to 10 (i.e. retrieve 10 records) and your server
     * return 15 then only 10 records will be loaded.
     *
     * @var int
     */
    protected $recordsPerPage = 15;

    /**
     * An array to construct a select box element in the pager in which we can change the number of the visible rows.
     * When changed during the execution, this parameter replaces the rowNum parameter that is passed to the url.
     * If the array is empty the element does not appear in the pager. Typical you can set this like [10,20,30].
     * If the rowNum parameter is set to 30 then the selected value in the select box is 30.
     *
     * @var array
     */
    protected $recordsPerPageList = array(5, 15, 25, 50);

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
    protected $scroll;

    /**
     * Determines the width of the vertical scrollbar. Since different browsers interpret this width differently
     * (and it is difficult to calculate it in all browsers) this can be changed.
     *
     * @var int
     */
    protected $scrollOffset;

    /**
     * When enabled, selecting a row with setSelection scrolls the grid so that the selected row is visible. This is
     * especially useful when we have a verticall scrolling grid and we use form editing with navigation buttons
     * (next or previous row). On navigating to a hidden row, the grid scrolls so the selected row becomes visible.
     *
     * @var bool
     */
    protected $scrollRows;

    /**
     * This control the timeout handler when scroll is set to 1. In miliseconds.
     *
     * @var int
     */
    protected $scrollTimeout;

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
    protected $shrinkToFit;

    /**
     * The column according to which the data is to be sorted when it is initially loaded from the server (note that you
     * will have to use datatypes xml or json to load remote data). This parameter is appended to the url. If this value
     * is set and the index (name) matches the name from colModel, then an icon indicating that the grid is sorted
     * according to this column is added to the column header.
     *
     * @var string
     */
    protected $sortName;

    /**
     * The initial sorting order (ascending or descending) when we fetch data from the server using datatypes xml or
     * json. This parameter is appended to the url - see prnNames. The two allowed values are - asc or desc.
     *
     * @var string
     */
    protected $sortOrder = self::SORT_ORDER_ASC;

    /**
     * When enabled this option allow column reordering with mouse. Since this option uses jQuery UI sortable widget,
     * be a sure that this widget and the related to widget files are loaded in head tag. Also be a sure too that you
     * mark the grid.jqueryui.js when you download the jqGrid.
     *
     * @var bool
     */
    protected $sortingColumns = true;

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
    protected $sortingColumnsDefinition = array(true, 'vertical', true);

    /**
     * Enables (disables) the tree grid format.
     *
     * @var bool
     */
    protected $treeGrid;

    /**
     * Deteremines the method used for the treeGrid. Can be 'nested'
     * or 'adjacency'
     *
     * @var string
     */
    protected $treeGridType;

    /**
     * This array set the icons used in the tree. The icons should be a valid
     * names from UI theme roller images.
     * The default values are:
     *
     * array(
     *  'plus' => 'ui-icon-triangle-1-e',
     *  'minus' => 'ui-icon-triangle-1-s',
     *  'leaf' => 'ui-icon-radio-off'
     * );
     *
     * @var string
     */
    protected $treeGridIcons;

    /**
     * The url of the file that holds the request
     *
     * @var string
     */
    protected $url;

    /**
     * This array contains custom information from the request. Can be used at
     * any time.
     *
     * @var array
     */
    protected $userData = array();

    /**
     * When set to true we directly place the user data array userData in
     * the footer. The rules are as follows: If the userData array contains
     * a name which matches any name defined in colModel, then the value is
     * placed in that column. If there are no such values nothing is placed.
     * Note that if this option is used we use the current formatter options
     * (if available) for that column.
     *
     * @var bool
     */
    protected $userDataOnFooter;

    /**
     * If this option is not set, the width of the grid is a sum of the widths
     * of the columns defined (in pixels).
     * If this option is set, the initial width of each column is set according
     * to the value of shrinkToFit option.
     *
     * @var int
     */
    protected $width;

    /**
     * @param boolean $alternativeRows
     * @return JqGridOptions
     */
    public function setAlternativeRows($alternativeRows)
    {
        $this->alternativeRows = $alternativeRows;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAlternativeRows()
    {
        return $this->alternativeRows;
    }

    /**
     * @param string $alternativeRowsClass
     * @return JqGridOptions
     */
    public function setAlternativeRowsClass($alternativeRowsClass)
    {
        $this->alternativeRowsClass = $alternativeRowsClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlternativeRowsClass()
    {
        return $this->alternativeRowsClass;
    }

    /**
     * @param boolean $autoEncodeIncomingAndPostData
     * @return JqGridOptions
     */
    public function setAutoEncodeIncomingAndPostData($autoEncodeIncomingAndPostData)
    {
        $this->autoEncodeIncomingAndPostData = $autoEncodeIncomingAndPostData;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAutoEncodeIncomingAndPostData()
    {
        return $this->autoEncodeIncomingAndPostData;
    }

    /**
     * @param boolean $autowidth
     * @return JqGridOptions
     */
    public function setAutowidth($autowidth)
    {
        $this->autowidth = $autowidth;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAutowidth()
    {
        return $this->autowidth;
    }

    /**
     * @param string $caption
     * @return JqGridOptions
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param boolean $cellEdit
     * @return JqGridOptions
     */
    public function setCellEdit($cellEdit)
    {
        $this->cellEdit = $cellEdit;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getCellEdit()
    {
        return $this->cellEdit;
    }

    /**
     * @param string $cellEditUrl
     * @return JqGridOptions
     */
    public function setCellEditUrl($cellEditUrl)
    {
        $this->cellEditUrl = $cellEditUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getCellEditUrl()
    {
        return $this->cellEditUrl;
    }

    /**
     * @param int $cellLayout
     * @return JqGridOptions
     */
    public function setCellLayout($cellLayout)
    {
        $this->cellLayout = $cellLayout;

        return $this;
    }

    /**
     * @return int
     */
    public function getCellLayout()
    {
        return $this->cellLayout;
    }

    /**
     * @param string $cellSaveType
     * @return JqGridOptions
     */
    public function setCellSaveType($cellSaveType)
    {
        $this->cellSaveType = $cellSaveType;

        return $this;
    }

    /**
     * @return string
     */
    public function getCellSaveType()
    {
        return $this->cellSaveType;
    }

    /**
     * @param string $cellSaveUrl
     * @return JqGridOptions
     */
    public function setCellSaveUrl($cellSaveUrl)
    {
        $this->cellSaveUrl = $cellSaveUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getCellSaveUrl()
    {
        return $this->cellSaveUrl;
    }

    /**
     * @param array $data
     * @return JqGridOptions
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $dataString
     * @return JqGridOptions
     */
    public function setDataString($dataString)
    {
        $this->dataString = $dataString;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataString()
    {
        return $this->dataString;
    }

    /**
     * @param  string $dataType
     * @throws Exception\InvalidArgumentException
     * @return JqGridOptions
     */
    public function setDataType($dataType)
    {
        $normalized = strtolower($dataType);

        if(!in_array($normalized, array('xml', 'xmlstring', 'json', 'jsonstring', 'local', 'javascript'))) {
            throw new Exception\InvalidArgumentException("DataType must be 'xml', 'xmlstring', 'json', 'jsonstring', 'local' or 'javascript'");
        }

        $this->dataType = $dataType;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $expandColumnIdentifier
     * @return JqGridOptions
     */
    public function setExpandColumnIdentifier($expandColumnIdentifier)
    {
        $this->expandColumnIdentifier = $expandColumnIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpandColumnIdentifier()
    {
        return $this->expandColumnIdentifier;
    }

    /**
     * @param boolean $expandColumnOnClick
     * @return JqGridOptions
     */
    public function setExpandColumnOnClick($expandColumnOnClick)
    {
        $this->expandColumnOnClick = $expandColumnOnClick;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getExpandColumnOnClick()
    {
        return $this->expandColumnOnClick;
    }

    /**
     * @param  bool $searchOnEnter
     * @param  bool $showOperators
     * @return JqGridOptions
     */
    public function setFilterToolbar($searchOnEnter, $showOperators)
    {
        $this->filterToolbar = array(
            'stringResult' => true,
            'searchOnEnter' => $searchOnEnter,
            'searchOperators' => $showOperators,
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getFilterToolbar()
    {
        return $this->filterToolbar;
    }

    /**
     * Set if filter toolbar search only on Enter
     *
     * @param  bool $searchOnEnter
     * @return JqGridOptions
     */
    public function setFilterToolbarSearchOnEnter($searchOnEnter)
    {
        $this->filterToolbar['searchOnEnter'] = (bool) $searchOnEnter;
        return $this;
    }

    /**
     * Filter toolbar only on Enter?
     *
     * @return bool
     */
    public function getFilterToolbarSearchOnEnter()
    {
        return $this->filterToolbar['searchOnEnter'];
    }

    /**
     * Set if filter toolbar show operators
     *
     * @param  bool $showOperators
     * @return JqGridOptions
     */
    public function setFilterToolbarShowOperators($showOperators)
    {
        $this->filterToolbar['searchOperators'] = (bool) $showOperators;
        return $this;
    }

    /**
     * Show operators in filer toolbar?
     *
     * @return bool
     */
    public function getFilterToolbarShowOperators()
    {
        return $this->filterToolbar['searchOperators'];
    }

    /**
     * Set if filter toolbar is enabled
     *
     * @param  bool $filterToolbarEnabled
     * @return JqGridOptions
     */
    public function setFilterToolbarEnabled($filterToolbarEnabled)
    {
        $this->filterToolbarEnabled = (bool) $filterToolbarEnabled;

        return $this;
    }

    /**
     * Is filter toolbar enabled?
     *
     * @return bool
     */
    public function getFilterToolbarEnabled()
    {
        return $this->filterToolbarEnabled;
    }

    /**
     * @param  bool $forceFit
     * @return JqGridOptions
     */
    public function setForceFit($forceFit)
    {
        $this->forceFit = $forceFit;

        return $this;
    }

    /**
     * @return bool
     */
    public function getForceFit()
    {
        return $this->forceFit;
    }

    /**
     * @param string $gridState
     * @return JqGridOptions
     */
    public function setGridState($gridState)
    {
        $this->gridState = $gridState;

        return $this;
    }

    /**
     * @return string
     */
    public function getGridState()
    {
        return $this->gridState;
    }

    /**
     * @param  boolean $gridView
     * @return JqGridOptions
     */
    public function setGridView($gridView)
    {
        $this->gridView = $gridView;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getGridView()
    {
        return $this->gridView;
    }

    /**
     * @param boolean $grouping
     * @return JqGridOptions
     */
    public function setGrouping($grouping)
    {
        $this->grouping = $grouping;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getGrouping()
    {
        return $this->grouping;
    }

    /**
     * @param  mixed $groupingView
     * @return JqGridOptions
     */
    public function setGroupingView($groupingView)
    {
        $this->groupingView = $groupingView;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroupingView()
    {
        return $this->groupingView;
    }

    /**
     * @param string $headerTitles
     * @return JqGridOptions
     */
    public function setHeaderTitles($headerTitles)
    {
        $this->headerTitles = $headerTitles;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeaderTitles()
    {
        return $this->headerTitles;
    }

    /**
     * @param string $height
     * @return JqGridOptions
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param boolean $hoverRows
     * @return JqGridOptions
     */
    public function setHoverRows($hoverRows)
    {
        $this->hoverRows = $hoverRows;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getHoverRows()
    {
        return $this->hoverRows;
    }

    /**
     * @param boolean $loadOnce
     * @return JqGridOptions
     */
    public function setLoadOnce($loadOnce)
    {
        $this->loadOnce = $loadOnce;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getLoadOnce()
    {
        return $this->loadOnce;
    }

    /**
     * @param string $loadType
     * @return JqGridOptions
     */
    public function setLoadType($loadType)
    {
        $this->loadType = $loadType;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoadType()
    {
        return $this->loadType;
    }

    /**
     * @param boolean $multiSelect
     * @return JqGridOptions
     */
    public function setMultiSelect($multiSelect)
    {
        $this->multiSelect = $multiSelect;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMultiSelect()
    {
        return $this->multiSelect;
    }

    /**
     * @param string $multiSelectKey
     * @return JqGridOptions
     */
    public function setMultiSelectKey($multiSelectKey)
    {
        $this->multiSelectKey = $multiSelectKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getMultiSelectKey()
    {
        return $this->multiSelectKey;
    }

    /**
     * @param int $multiSelectWidth
     * @return JqGridOptions
     */
    public function setMultiSelectWidth($multiSelectWidth)
    {
        $this->multiSelectWidth = $multiSelectWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getMultiSelectWidth()
    {
        return $this->multiSelectWidth;
    }

    /**
     * @param  boolean $multiSort
     * @return JqGridOptions
     */
    public function setMultiSort($multiSort)
    {
        $this->multiSort = $multiSort;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMultiSort()
    {
        return $this->multiSort;
    }

    /**
     * @param  int $page
     * @return JqGridOptions
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param string $pagerElementId
     * @return JqGridOptions
     */
    public function setPagerElementId($pagerElementId)
    {
        $this->pagerElementId = $pagerElementId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerElementId()
    {
        return $this->pagerElementId;
    }

    /**
     * @param string $pagerPosition
     * @return JqGridOptions
     */
    public function setPagerPosition($pagerPosition)
    {
        $this->pagerPosition = $pagerPosition;

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerPosition()
    {
        return $this->pagerPosition;
    }

    /**
     * @param boolean $pagerShowButtons
     * @return JqGridOptions
     */
    public function setPagerShowButtons($pagerShowButtons)
    {
        $this->pagerShowButtons = $pagerShowButtons;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPagerShowButtons()
    {
        return $this->pagerShowButtons;
    }

    /**
     * @param boolean $pagerShowInput
     * @return JqGridOptions
     */
    public function setPagerShowInput($pagerShowInput)
    {
        $this->pagerShowInput = $pagerShowInput;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPagerShowInput()
    {
        return $this->pagerShowInput;
    }

    /**
     * @param string $recordPosition
     * @return JqGridOptions
     */
    public function setRecordPosition($recordPosition)
    {
        $this->recordPosition = $recordPosition;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecordPosition()
    {
        return $this->recordPosition;
    }

    /**
     * @param int $recordsPerPage
     * @return JqGridOptions
     */
    public function setRecordsPerPage($recordsPerPage)
    {
        $this->recordsPerPage = $recordsPerPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getRecordsPerPage()
    {
        return $this->recordsPerPage;
    }

    /**
     * @param array $recordsPerPageList
     * @return JqGridOptions
     */
    public function setRecordsPerPageList($recordsPerPageList)
    {
        $this->recordsPerPageList = $recordsPerPageList;

        return $this;
    }

    /**
     * @return array
     */
    public function getRecordsPerPageList()
    {
        return $this->recordsPerPageList;
    }

    /**
     * @param boolean $renderFooterRow
     * @return JqGridOptions
     */
    public function setRenderFooterRow($renderFooterRow)
    {
        $this->renderFooterRow = $renderFooterRow;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRenderFooterRow()
    {
        return $this->renderFooterRow;
    }

    /**
     * @param boolean $renderHideGridButton
     * @return JqGridOptions
     */
    public function setRenderHideGridButton($renderHideGridButton)
    {
        $this->renderHideGridButton = $renderHideGridButton;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRenderHideGridButton()
    {
        return $this->renderHideGridButton;
    }

    /**
     * @param boolean $renderRecordsInfo
     * @return JqGridOptions
     */
    public function setRenderRecordsInfo($renderRecordsInfo)
    {
        $this->renderRecordsInfo = $renderRecordsInfo;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRenderRecordsInfo()
    {
        return $this->renderRecordsInfo;
    }

    /**
     * @param boolean $renderRowNumbersColumn
     * @return JqGridOptions
     */
    public function setRenderRowNumbersColumn($renderRowNumbersColumn)
    {
        $this->renderRowNumbersColumn = $renderRowNumbersColumn;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRenderRowNumbersColumn()
    {
        return $this->renderRowNumbersColumn;
    }

    /**
     * @param string $requestType
     * @return JqGridOptions
     */
    public function setRequestType($requestType)
    {
        $this->requestType = $requestType;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @param string $resizeClass
     * @return JqGridOptions
     */
    public function setResizeClass($resizeClass)
    {
        $this->resizeClass = $resizeClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getResizeClass()
    {
        return $this->resizeClass;
    }

    /**
     * @param bool|int $scroll
     * @return JqGridOptions
     */
    public function setScroll($scroll)
    {
        $this->scroll = $scroll;

        return $this;
    }

    /**
     * @return bool|int
     */
    public function getScroll()
    {
        return $this->scroll;
    }

    /**
     * @param int $scrollOffset
     * @return JqGridOptions
     */
    public function setScrollOffset($scrollOffset)
    {
        $this->scrollOffset = $scrollOffset;

        return $this;
    }

    /**
     * @return int
     */
    public function getScrollOffset()
    {
        return $this->scrollOffset;
    }

    /**
     * @param boolean $scrollRows
     * @return JqGridOptions
     */
    public function setScrollRows($scrollRows)
    {
        $this->scrollRows = $scrollRows;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getScrollRows()
    {
        return $this->scrollRows;
    }

    /**
     * @param  int $scrollTimeout
     * @return JqGridOptions
     */
    public function setScrollTimeout($scrollTimeout)
    {
        $this->scrollTimeout = $scrollTimeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getScrollTimeout()
    {
        return $this->scrollTimeout;
    }

    /**
     * @param  bool $shrinkToFit
     * @return JqGridOptions
     */
    public function setShrinkToFit($shrinkToFit)
    {
        $this->shrinkToFit = $shrinkToFit;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getShrinkToFit()
    {
        return $this->shrinkToFit;
    }

    /**
     * @param  string $sortName
     * @return JqGridOptions
     */
    public function setSortName($sortName)
    {
        $this->sortName = $sortName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortName()
    {
        return $this->sortName;
    }

    /**
     * @param  string $sortOrder
     * @throws Exception\InvalidArgumentException
     * @return JqGridOptions
     */
    public function setSortOrder($sortOrder)
    {
        $order = (string) $sortOrder;
        $order = strtolower($order);

        if(!in_array($order, array(self::SORT_ORDER_ASC, self::SORT_ORDER_DESC))) {
            throw new Exception\InvalidArgumentException("Order must by 'asc' or 'desc'");
        }

        $this->sortOrder = $order;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param boolean $sortingColumns
     * @return JqGridOptions
     */
    public function setSortingColumns($sortingColumns)
    {
        $this->sortingColumns = $sortingColumns;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSortingColumns()
    {
        return $this->sortingColumns;
    }

    /**
     * @param array $sortingColumnsDefinition
     * @return JqGridOptions
     */
    public function setSortingColumnsDefinition($sortingColumnsDefinition)
    {
        $this->sortingColumnsDefinition = $sortingColumnsDefinition;

        return $this;
    }

    /**
     * @return array
     */
    public function getSortingColumnsDefinition()
    {
        return $this->sortingColumnsDefinition;
    }

    /**
     * @param boolean $treeGrid
     * @return JqGridOptions
     */
    public function setTreeGrid($treeGrid)
    {
        $this->treeGrid = $treeGrid;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getTreeGrid()
    {
        return $this->treeGrid;
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
     * @return JqGridOptions
     */
    public function setTreeGridIcons($plus, $minus, $leaf)
    {
        $this->treeGridIcons = array(
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
        return $this->treeGridIcons;
    }

    /**
     * @param string $treeGridType
     * @return JqGridOptions
     */
    public function setTreeGridType($treeGridType)
    {
        $this->treeGridType = $treeGridType;

        return $this;
    }

    /**
     * @return string
     */
    public function getTreeGridType()
    {
        return $this->treeGridType;
    }

    /**
     * @param  string $url
     * @return JqGridOptions
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param  array $userData
     * @return JqGridOptions
     */
    public function setUserData(array $userData)
    {
        $this->userData = $userData;

        return $this;
    }

    /**
     * @return array
     */
    public function getUserData()
    {
        return $this->userData;
    }

    /**
     * @param  bool $userDataOnFooter
     * @return JqGridOptions
     */
    public function setUserDataOnFooter($userDataOnFooter)
    {
        $this->userDataOnFooter = (bool) $userDataOnFooter;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUserDataOnFooter()
    {
        return $this->userDataOnFooter;
    }

    /**
     * @param  int $width
     * @return JqGridOptions
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }
}
