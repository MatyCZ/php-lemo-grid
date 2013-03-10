<?php

namespace LemoGrid\Column;

use Zend\Stdlib\AbstractOptions;

class ColumnAttributes extends AbstractOptions
{
    /**
     * Defines the alignment of the cell in the Body layer, not in header cell. Possible values: left, center, right.
     *
     * @var string
     */
    protected $align;

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
    protected $columnAttributes;

    /**
     * This option allow to add classes to the column. If more than one class will be used a space should be set.
     * By example classes:'class1 class2' will set a class1 and class2 to every cell on that column. In the grid css
     * there is a predefined class ui-ellipsis which allow to attach ellipsis to a particular row. Also this will work
     * in FireFox too.
     *
     * @var string
     */
    protected $class;

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
    protected $dateFormat;

    /**
     * The default value for the search field. This option is used only in Custom Searching and will be set as initial
     * search.
     *
     * @var string
     */
    protected $defaultValue;

    /**
     * Defines the edit type for inline and form editing Possible values: text, textarea, select, checkbox, password,
     * button, image and file.
     *
     * @var string
     */
    protected $editElement;

    /**
     * Defines various options for form editing.
     *
     * @var array
     */
    protected $editElementOptions;

    /**
     * Array of allowed options (attributes) for edittype option.
     *
     * @var array
     */
    protected $editOptions;

    /**
     * Sets additional rules for the editable column.
     *
     * @var array
     */
    protected $editRules;

    /**
     * The predefined types (string) or custom function name that controls the format of this field.
     *
     * @var mixed
     */
    protected $format;

    /**
     * Format options can be defined for particular columns, overwriting the defaults from the language file.
     *
     * @var array
     */
    protected $formatOptions;

    /**
     * Set the index name when sorting. Passed as sidx parameter.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Defines if the field is editable. This option is used in cell, inline and form modules.
     *
     * @var bool
     */
    protected $isEditable;

    /**
     * If set to true this option does not allow recalculation of the width of the column if shrinkToFit option is set
     * to true. Also the width does not change if a setGridWidth method is used to change the grid width.
     *
     * @var bool
     */
    protected $isFixed;

    /**
     * If set to true determines that this column will be frozen after calling the setFrozenColumns method.
     *
     * @var bool
     */
    protected $isFrozen;

    /**
     * Defines if this column is hidden at initialization.
     *
     * @var bool
     */
    protected $isHidden;

    /**
     * If set to true this column will not appear in the modal dialog where users can choose which columns to show
     * or hide.
     *
     * @var bool
     */
    protected $isHideable;

    /**
     * Defines if the column can be re sized.
     *
     * @var bool
     */
    protected $isResizable = false;

    /**
     * When used in search modules, disables or enables searching on that column.
     *
     * @var bool
     */
    protected $isSearchable = true;

    /**
     * Defines is this can be sorted.
     *
     * @var string
     */
    protected $isSortable = true;

    /**
     * When colNames array is empty, defines the heading for this column. If both the colNames array and this setting
     * are empty, the heading for this column comes from the name property.
     *
     * @var string
     */
    protected $label;

    /**
     * Determines the type of the element when searching. Possible values: text and select.
     *
     * @var string
     */
    protected $searchElement = 'text';

    /**
     * Defines the search options used searching.
     *
     * @var array
     */
    protected $searchOptions;

    /**
     * Valid only in Custom Searching and edittype : 'select' and describes the url from where we can get
     * already-constructed select element.
     *
     * @var string
     */
    protected $searchUrl;

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
    protected $sortType;

    /**
     * Set the initial width of the column, in pixels. This value currently can not be set as percentage.
     *
     * @var int
     */
    protected $width;

    /**
     * @param string $align
     * @return ColumnAttributes
     */
    public function setAlign($align)
    {
        $this->align = $align;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlign()
    {
        return $this->align;
    }

    /**
     * @param string $class
     * @return ColumnAttributes
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $dateFormat
     * @return ColumnAttributes
     */
    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @param string $defaultValue
     * @return ColumnAttributes
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string $editElement
     * @return ColumnAttributes
     */
    public function setEditElement($editElement)
    {
        $this->editElement = $editElement;

        return $this;
    }

    /**
     * @return string
     */
    public function getEditElement()
    {
        return $this->editElement;
    }

    /**
     * @param array $editElementOptions
     * @return ColumnAttributes
     */
    public function setEditElementOptions($editElementOptions)
    {
        $this->editElementOptions = $editElementOptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getEditElementOptions()
    {
        return $this->editElementOptions;
    }

    /**
     * @param array $editOptions
     * @return ColumnAttributes
     */
    public function setEditOptions($editOptions)
    {
        $this->editOptions = $editOptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getEditOptions()
    {
        return $this->editOptions;
    }

    /**
     * @param array $editRules
     * @return ColumnAttributes
     */
    public function setEditRules($editRules)
    {
        $this->editRules = $editRules;

        return $this;
    }

    /**
     * @return array
     */
    public function getEditRules()
    {
        return $this->editRules;
    }

    /**
     * @param mixed $format
     * @return ColumnAttributes
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param  array $formatOptions
     * @return ColumnAttributes
     */
    public function setFormatOptions($formatOptions)
    {
        $this->formatOptions = $formatOptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getFormatOptions()
    {
        return $this->formatOptions;
    }

    /**
     * @param  string $identifier
     * @return ColumnAttributes
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = (string) $identifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param boolean $isEditable
     * @return ColumnAttributes
     */
    public function setIsEditable($isEditable)
    {
        $this->isEditable = $isEditable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsEditable()
    {
        return $this->isEditable;
    }

    /**
     * @param boolean $isFixed
     * @return ColumnAttributes
     */
    public function setIsFixed($isFixed)
    {
        $this->isFixed = $isFixed;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsFixed()
    {
        return $this->isFixed;
    }

    /**
     * @param boolean $isFrozen
     * @return ColumnAttributes
     */
    public function setIsFrozen($isFrozen)
    {
        $this->isFrozen = $isFrozen;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsFrozen()
    {
        return $this->isFrozen;
    }

    /**
     * @param boolean $isHidden
     * @return ColumnAttributes
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsHidden()
    {
        return $this->isHidden;
    }

    /**
     * @param boolean $isResizable
     * @return ColumnAttributes
     */
    public function setIsResizable($isResizable)
    {
        $this->isResizable = $isResizable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsResizable()
    {
        return $this->isResizable;
    }

    /**
     * @param boolean $isHideable
     * @return ColumnAttributes
     */
    public function setIsHideable($isHideable)
    {
        $this->isHideable = $isHideable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsHideable()
    {
        return $this->isHideable;
    }

    /**
     * @param boolean $isSearchable
     * @return ColumnAttributes
     */
    public function setIsSearchable($isSearchable)
    {
        $this->isSearchable = $isSearchable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsSearchable()
    {
        return $this->isSearchable;
    }

    /**
     * @param string $isSortable
     * @return ColumnAttributes
     */
    public function setIsSortable($isSortable)
    {
        $this->isSortable = $isSortable;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsSortable()
    {
        return $this->isSortable;
    }

    /**
     * @param string $label
     * @return ColumnAttributes
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $searchElement
     * @return ColumnAttributes
     */
    public function setSearchElement($searchElement)
    {
        $this->searchElement = $searchElement;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchElement()
    {
        return $this->searchElement;
    }

    /**
     * @param string $searchUrl
     * @return ColumnAttributes
     */
    public function setSearchUrl($searchUrl)
    {
        $this->searchUrl = $searchUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->searchUrl;
    }

    /**
     * @param array $searchOptions
     * @return ColumnAttributes
     */
    public function setSearchOptions($searchOptions)
    {
        $this->searchOptions = $searchOptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchOptions()
    {
        return $this->searchOptions;
    }

    /**
     * @param string $sortType
     * @return ColumnAttributes
     */
    public function setSortType($sortType)
    {
        $this->sortType = $sortType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortType()
    {
        return $this->sortType;
    }

    /**
     * @param int $width
     * @return ColumnAttributes
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
