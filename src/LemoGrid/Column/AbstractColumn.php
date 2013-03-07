<?php

/**
 * @namespace
 */
namespace LemoBase\Grid\Column;

use LemoBase\Grid\Grid,
    LemoBase\Grid\Column\ColumnInterface,
    LemoBase\Grid\Column\Exception as ColumnException,
    Traversable,
    Zend\Config\Config,
    Zend\Filter\Filter,
    Zend\Loader\PrefixPathLoader,
    Zend\Loader\PrefixPathMapper,
    Zend\Stdlib\IteratorToArray,
    Zend\Translator,
    Zend\View\Renderer\PhpRenderer,
    Zend\View\Renderer as View;

/**
 * LemoBase_Grid_Column
 *
 * @category   LemoBase
 * @package    LemoBase_Grid
 * @subpackage Column
 */
abstract class AbstractColumn implements ColumnInterface
{
    const ALIGN_CENTER = 'center';
    const ALIGN_LEFT   = 'left';
    const ALIGN_RIGHT  = 'right';

    const EDIT_ELEMENT_BUTTON    = 'button';
    const EDIT_ELEMENT_CHECKBOX  = 'checkbox';
    const EDIT_ELEMENT_FILE      = 'file';
    const EDIT_ELEMENT_IMAGE     = 'image';
    const EDIT_ELEMENT_PASSWORD = 'password';
    const EDIT_ELEMENT_SELECT    = 'select';
    const EDIT_ELEMENT_TEXT      = 'text';
    const EDIT_ELEMENT_TEXTAREA  = 'textarea';

    const SEARCH_ELEMENT_SELECT = 'select';
    const SEARCH_ELEMENT_TEXT   = 'text';

    const SORT_TYPE_CURRENCY = 'currency';
    const SORT_TYPE_DATE     = 'date';
    const SORT_TYPE_FLOAT    = 'float';
    const SORT_TYPE_INT      = 'int';
    const SORT_TYPE_INTEGER  = 'integer';
    const SORT_TYPE_NUMBER   = 'number';
    const SORT_TYPE_TEXT     = 'text';

    /**
     * @var \LemoBase\Grid\Grid
     */
    protected $_grid = null;

    /**
     * Column name
     *
     * @var string
     */
    protected $_name;

    /**
     * Order of column
     *
     * @var int
     */
    protected $_order;

    /**
     * Column type
     *
     * @var string
     */
    protected $_type;

    /**
     * List of column options
     *
     * @var array
     */
    private $_columnOptions = array(
        'align' => null,
        'columnAttributes' => 'cellattr',
        'class' => 'classes',
        'dateFormat' => 'datefmt',
        'defaultValue' => 'defval',
        'editElement' => 'edittype',
        'editElementOptions' => 'formoptions',
        'editOptions' => null,
        'editRules' => null,
        'format' => 'formatter',
        'formatOptions' => null,
//		'identifier' => null,
        'isEditable' => null,
        'isFixed' => 'page',
        'isFrozen' => 'frozen',
        'isHidden' => 'hidden',
        'isHideable' => 'hidedlg',
        'isSearchable' => 'search',
        'isSortable' => 'sortable',
        'isResizable' => 'resizable',
        //'label' => null,
        'name' => null,
        'searchElement' => 'stype',
        'searchOptions' => null,
        'searchUrl' => 'surl',
        'sortType' => null,
        'width' => null,
    );

    // ===== GRID COLUMN PROPERTIES =====

    /**
     * Defines the alignment of the cell in the Body layer, not in header cell. Possible values: left, center, right.
     *
     * @var string
     */
    protected $_align = null;

    /**
     * This function add attributes to the cell during the creation of the data - i.e dynamically. By example all valid
     * attributes for the table cell can be used or a style attribute with different properties. The function should
     * return string. Parameters passed to this function are:
     * - rowId - the id of the row
     * - val - the value which will be added in the cell
     * - rawObject - the raw object of the data row - i.e if datatype is json - array, if datatype is xml xml node.
     * - cm - all the properties of this column listed in the colModel
     * - rdata - the data row which will be inserted in the row. This parameter is array of type name:value, where name is the name in colModel
     *
     * @var array
     */
    protected $_columnAttributes = null;

    /**
     * This option allow to add classes to the column. If more than one class will be used a space should be set.
     * By example classes:'class1 class2' will set a class1 and class2 to every cell on that column. In the grid css
     * there is a predefined class ui-ellipsis which allow to attach ellipsis to a particular row. Also this will work
     * in FireFox too.
     *
     * @var string
     */
    protected $_class = null;

    /**
     * Governs format of sorttype:date (when datetype is set to local) and editrules {date:true} fields. Determines the
     * expected date format for that column. Uses a PHP-like date formatting. Currently ”/”, ”-”, and ”.” are supported
     * as date separators. Valid formats are:
     * - y,Y,yyyy for four digits year
     * - YY, yy for two digits year
     * - m,mm for months
     * - d,dd for days.
     *
     * @var string
     */
    protected $_dateFormat = null;

    /**
     * The default value for the search field. This option is used only in Custom Searching and will be set as initial
     * search.
     *
     * @var string
     */
    protected $_defaultValue = null;

    /**
     * Defines the edit type for inline and form editing Possible values: text, textarea, select, checkbox, password,
     * button, image and file.
     *
     * @var string
     */
    protected $_editElement = null;

    /**
     * Defines various options for form editing.
     *
     * @var array
     */
    protected $_editElementOptions = null;

    /**
     * Array of allowed options (attributes) for edittype option.
     *
     * @var array
     */
    protected $_editOptions = null;

    /**
     * Sets additional rules for the editable column.
     *
     * @var array
     */
    protected $_editRules = null;

    /**
     * The predefined types (string) or custom function name that controls the format of this field.
     *
     * @var mixed
     */
    protected $_format = null;

    /**
     * Format options can be defined for particular columns, overwriting the defaults from the language file.
     *
     * @var array
     */
    protected $_formatOptions = null;

    /**
     * Set the index name when sorting. Passed as sidx parameter.
     *
     * @var string
     */
    protected $_identifier = null;

    /**
     * Defines if the field is editable. This option is used in cell, inline and form modules.
     *
     * @var bool
     */
    protected $_isEditable = null;

    /**
     * If set to true this option does not allow recalculation of the width of the column if shrinkToFit option is set
     * to true. Also the width does not change if a setGridWidth method is used to change the grid width.
     *
     * @var bool
     */
    protected $_isFixed = null;

    /**
     * If set to true determines that this column will be frozen after calling the setFrozenColumns method.
     *
     * @var bool
     */
    protected $_isFrozen = null;

    /**
     * Defines if this column is hidden at initialization.
     *
     * @var bool
     */
    protected $_isHidden = null;

    /**
     * If set to true this column will not appear in the modal dialog where users can choose which columns to show
     * or hide.
     *
     * @var bool
     */
    protected $_isHideable = null;

    /**
     * Defines if the column can be re sized.
     *
     * @var bool
     */
    protected $_isResizable = false;

    /**
     * When used in search modules, disables or enables searching on that column.
     *
     * @var bool
     */
    protected $_isSearchable = true;

    /**
     * Defines is this can be sorted.
     *
     * @var string
     */
    protected $_isSortable = true;

    /**
     * When colNames array is empty, defines the heading for this column. If both the colNames array and this setting
     * are empty, the heading for this column comes from the name property.
     *
     * @var string
     */
    protected $_label = null;

    /**
     * Determines the type of the element when searching. Possible values: text and select.
     *
     * @var string
     */
    protected $_searchElement = 'text';

    /**
     * Defines the search options used searching.
     *
     * @var array
     */
    protected $_searchOptions = null;

    /**
     * Valid only in Custom Searching and edittype : 'select' and describes the url from where we can get
     * already-constructed select element.
     *
     * @var string
     */
    protected $_searchUrl = null;

    /**
     * Used when datatype is local. Defines the type of the column for appropriate sorting. Possible values:
     * - int/integer - for sorting integer
     * - float/number/currency - for sorting decimal numbers
     * - date - for sorting date
     * - text - for text sorting
     * - function - defines a custom function for sorting.
     *
     * To this function we pass the value to be sorted and it should return a value too.
     *
     * @var string
     */
    protected $_sortType = null;

    /**
     * Set the initial width of the column, in pixels. This value currently can not be set as percentage.
     *
     * @var int
     */
    protected $_width = null;

    /**
     * Constructor
     *
     * $spec may be:
     * - string: name of column
     * - array: options with which to configure column
     * - Zend_Config: Zend_Config with options for configuring column
     *
     * @param  string|array|Config $spec
     * @param  array|Traversable $options
     * @return void
     * @throws ColumnException if no column name after initialization
     */
    public function __construct($spec, $options = null)
    {
        if ($spec instanceof Traversable) {
            $spec = IteratorToArray::convert($spec);
        }
        if (is_string($spec)) {
            $this->setName($spec);
        } elseif (is_array($spec)) {
            $this->setOptions($spec);
        }
        if ($options instanceof Traversable) {
            $options = IteratorToArray::convert($options);
        }
        if (is_string($spec) && is_array($options)) {
            $this->setOptions($options);
        }

        if (null === $this->getName()) {
            throw new ColumnException\UnexpectedValueException('LemoBase_Grid_Column requires each element to have a name');
        }

        /**
         * Extensions
         */
        $this->init();
    }

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set object state from options array
     *
     * @param  array $options
     * @return Column
     */
    public function setOptions(array $options)
    {
        if (isset($options['disableTranslator'])) {
            $this->setDisableTranslator($options['disableTranslator']);
            unset($options['disableTranslator']);
        }
        if (isset($options['grid'])) {
            $this->setGrid($options['grid']);
            unset($options['grid']);
        }

        unset($options['options']);
        unset($options['config']);

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (in_array($method, array('setTranslator', 'setView'))) {
                if (!is_object($value)) {
                    continue;
                }
            }

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set object state from Zend_Config object
     *
     * @param  Config $config
     * @return Column
     */
    public function setConfig(Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * @param \LemoBase\Grid\Grid $grid
     * @return AbstractColumn
     */
    public function setGrid(Grid $grid)
    {
        $this->_grid = $grid;

        return $this;
    }

    /**
     * @return \LemoBase\Grid\Grid
     */
    public function getGrid()
    {
        return $this->_grid;
    }

    /**
     * Render column options
     *
     * @return string
     */
    public function render()
    {
        $script = array();
        $attribs = array();

        foreach($this->_columnOptions as $nameProperty => $nameGrid) {
            $methodName = 'get' . ucfirst($nameProperty);

            if(method_exists($this, $methodName)) {
                $value = call_user_func(array($this, $methodName));

                if(null === $value) {
                }

                if(!empty($value) || is_bool($value)) {
                    if(null !== $nameGrid) {
                        $nameProperty = $nameGrid;
                    }

                    $attribs[strtolower($nameProperty)] = $value;
                }
            }
        }

        ksort($attribs);

        // Pokud nebyly definovany volby pro vychozi volbu
        if(!isset($attribs['searchoptions']) && $this->getGrid()->getQueryParam($this->getIdentifier())) {
            $attribs['searchoptions'] = array('defaultValue' => $this->getGrid()->getQueryParam($this->getIdentifier()));
        }

        foreach($attribs as $key => $value)
        {
            $inside = '';
            $values = array();

            if(is_array($value))
            {
                foreach($value as $k => $val)
                {
                    if(is_bool($val)) {
                        if($val == true) {
                            $val = 'true';
                        } else {
                            $val = 'false';
                        }
                    }

                    // Pokud je hodnota 0 a jedna se o polozku pro searchoptions
                    if(!is_string($k) AND $k == 0 AND $val != '-' AND $key == 'searchoptions') {
                        $k = '0';
                    }

                    if(is_numeric($val) AND $k != 'defaultValue' AND !in_array($key, array('editoptions', 'formatoptions', 'searchoptions')) OR is_numeric($val) AND $key == 'editoptions' AND $attribs['formatter'] == 'checkbox') {
                        $values[] = $val;
                    } elseif($key == 'searchoptions' AND $k != 'defaultValue') {
                        $values[] = $k . ":" .  $val;
                    } elseif($key == 'formatoptions' OR $key == 'editoptions' AND $attribs['formatter'] != 'checkbox') {
                        $values[] = $k . ":'" .  $val . "'";
                    } elseif($k != 'defaultValue') {
                        $values[] = "'" .  $val . "'";
                    }
                }
                if($key == 'searchoptions') {
                    if($this->getGrid()->getQueryParam($this->getIdentifier())) {
                        $inside = "defaultValue: '" . $this->getGrid()->getQueryParam($this->getIdentifier()) . "'";
                    }
                    if($inside != '' AND count($values) > 0) {
                        $inside .= ', ';
                    }
                    if(count($values) > 0) {
                        $inside .= 'value:"' . implode(';', $values) . '"';
                    }
                    $script[] .= $key . ': {' . $inside . '}';
                }elseif($key == 'editoptions') {
                    if($attribs['formatter'] == 'checkbox') {
                        $script[] .= $key . ': {value:\'' . implode(':', $values) . '\'}';
                    } else {
                        $script[] .= $key . ': {' . implode(',', $values) . '}';
                    }
                } elseif($key == 'formatoptions') {
                    $script[] .= $key . ': {' . implode(',', $values) . '}';
                } else {
                    $script[] .= $key . ': [' . implode(',', $values) . ']';
                }
            } elseif(is_numeric($value)) {
                $script[] .= $key . ': ' . $value;
            } elseif(is_bool($value)) {
                if($value == true) {
                    $value = 'true';
                } else {
                    $value = 'false';
                }
                $script[] .= $key . ': ' . $value;
            } else {
                $script[] .= $key . ': \'' . $value . '\'';
            }
        }

        return implode(', ', $script);
    }

    // Metadata

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
     * Set column name
     *
     * @param  string $name
     * @return Column
     */
    public function setName($name)
    {
        $name = $this->filterName($name);
        if ('' === $name) {
            throw new ColumnException\InvalidArgumentException('Invalid name provided; must contain only valid variable characters and be non-empty');
        }

        $this->_name = $name;

        return $this;
    }

    /**
     * Return column name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set column order
     *
     * @param  int $order
     * @return Column
     */
    public function setOrder($order)
    {
        $this->_order = (int) $order;

        return $this;
    }

    /**
     * Retrieve column order
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Return column type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    // Rendering

    /**
     * Set view object
     *
     * @param  View $view
     * @return Element
     */
    public function setView(View $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieve view object
     *
     * Instantiates a PhpRenderer if no View previously set.
     *
     * @return null|View
     */
    public function getView()
    {
        if (null === $this->_view) {
            $this->_view = $this->getGrid()->getView();
        }

        return $this->_view;
    }

    /**
     * Translate an option
     *
     * @param  string $option
     * @param  string $value
     * @return bool
     */
    protected function _translateOption($option, $value)
    {
        if ($this->translatorIsDisabled()) {
            return false;
        }

        if (!isset($this->_translated[$option]) && !empty($value)) {
            $this->options[$option] = $this->_translateValue($value);
            if ($this->options[$option] === $value) {
                return false;
            }
            $this->_translated[$option] = true;
            return true;
        }

        return false;
    }

    /**
     * Translate a multi option value
     *
     * @param  string $value
     * @return string
     */
    protected function _translateValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->_translateValue($val);
            }
            return $value;
        } else {
            if (null !== ($translator = $this->getTranslator())) {
                return $translator->translate($value);
            }

            return $value;
        }
    }

    /**
     * @param string $align
     * @return AbstractColumn
     */
    public function setAlign($align)
    {
        $this->_align = $align;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlign()
    {
        return $this->_align;
    }

    /**
     * @param string $class
     * @return AbstractColumn
     */
    public function setClass($class)
    {
        $this->_class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * @param string $dateFormat
     * @return AbstractColumn
     */
    public function setDateFormat($dateFormat)
    {
        $this->_dateFormat = $dateFormat;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    /**
     * @param string $defaultValue
     * @return AbstractColumn
     */
    public function setDefaultValue($defaultValue)
    {
        $this->_defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    /**
     * @param string $editElement
     * @return AbstractColumn
     */
    public function setEditElement($editElement)
    {
        $this->_editElement = $editElement;

        return $this;
    }

    /**
     * @return string
     */
    public function getEditElement()
    {
        return $this->_editElement;
    }

    /**
     * @param array $editElementOptions
     * @return AbstractColumn
     */
    public function setEditElementOptions($editElementOptions)
    {
        $this->_editElementOptions = $editElementOptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getEditElementOptions()
    {
        return $this->_editElementOptions;
    }

    /**
     * @param array $editOptions
     * @return AbstractColumn
     */
    public function setEditOptions($editOptions)
    {
        $this->_editOptions = $editOptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getEditOptions()
    {
        return $this->_editOptions;
    }

    /**
     * @param array $editRules
     * @return AbstractColumn
     */
    public function setEditRules($editRules)
    {
        $this->_editRules = $editRules;

        return $this;
    }

    /**
     * @return array
     */
    public function getEditRules()
    {
        return $this->_editRules;
    }

    /**
     * @param mixed $format
     * @return AbstractColumn
     */
    public function setFormat($format)
    {
        $this->_format = $format;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * @param array $formatOptions
     * @return AbstractColumn
     */
    public function setFormatOptions($formatOptions)
    {
        $this->_formatOptions = $formatOptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getFormatOptions()
    {
        return $this->_formatOptions;
    }

    /**
     * @param string $identifier
     * @return AbstractColumn
     */
    public function setIdentifier($identifier)
    {
        $this->_identifier = $identifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier($returnNameOnEmpty = true)
    {
        if(null === $this->_identifier && true === $returnNameOnEmpty) {
            return $this->getName();
        }

        return $this->_identifier;
    }

    /**
     * @param boolean $isEditable
     * @return AbstractColumn
     */
    public function setIsEditable($isEditable)
    {
        $this->_isEditable = $isEditable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsEditable()
    {
        return $this->_isEditable;
    }

    /**
     * @param boolean $isFixed
     * @return AbstractColumn
     */
    public function setIsFixed($isFixed)
    {
        $this->_isFixed = $isFixed;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsFixed()
    {
        return $this->_isFixed;
    }

    /**
     * @param boolean $isFrozen
     * @return AbstractColumn
     */
    public function setIsFrozen($isFrozen)
    {
        $this->_isFrozen = $isFrozen;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsFrozen()
    {
        return $this->_isFrozen;
    }

    /**
     * @param boolean $isHidden
     * @return AbstractColumn
     */
    public function setIsHidden($isHidden)
    {
        $this->_isHidden = $isHidden;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsHidden()
    {
        return $this->_isHidden;
    }

    /**
     * @param boolean $isResizable
     * @return AbstractColumn
     */
    public function setIsResizable($isResizable)
    {
        $this->_isResizable = $isResizable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsResizable()
    {
        return $this->_isResizable;
    }

    /**
     * @param boolean $isHideable
     * @return AbstractColumn
     */
    public function setIsHideable($isHideable)
    {
        $this->_isHideable = $isHideable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsHideable()
    {
        return $this->_isHideable;
    }

    /**
     * @param boolean $isSearchable
     * @return AbstractColumn
     */
    public function setIsSearchable($isSearchable)
    {
        $this->_isSearchable = $isSearchable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsSearchable()
    {
        return $this->_isSearchable;
    }

    /**
     * @param string $isSortable
     * @return AbstractColumn
     */
    public function setIsSortable($isSortable)
    {
        $this->_isSortable = $isSortable;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsSortable()
    {
        return $this->_isSortable;
    }

    /**
     * @param string $label
     * @return AbstractColumn
     */
    public function setLabel($label)
    {
        $this->_label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @param string $searchElement
     * @return AbstractColumn
     */
    public function setSearchElement($searchElement)
    {
        $this->_searchElement = $searchElement;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchElement()
    {
        return $this->_searchElement;
    }

    /**
     * @param string $searchUrl
     * @return AbstractColumn
     */
    public function setSearchUrl($searchUrl)
    {
        $this->_searchUrl = $searchUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->_searchUrl;
    }

    /**
     * @param array $searchOptions
     * @return AbstractColumn
     */
    public function setSearchOptions($searchOptions)
    {
        $this->_searchOptions = $searchOptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchOptions()
    {
        $options = array();

        if(is_array($this->_searchOptions)) {
            foreach($this->_searchOptions as $key => $value) {
                $options[$key] = $this->getGrid()->getView()->translate($value);
            }
        }

        return $options;
    }

    /**
     * @param string $sortType
     * @return AbstractColumn
     */
    public function setSortType($sortType)
    {
        $this->_sortType = $sortType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortType()
    {
        return $this->_sortType;
    }

    /**
     * @param int $width
     * @return AbstractColumn
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


    // ==== GRID PROPERTIES SETTERS / GETTERS ====

}
