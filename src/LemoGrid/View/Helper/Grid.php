<?php

namespace LemoGrid\View\Helper;

use LemoGrid\Exception;
use LemoGrid\GridInterface;
use LemoGrid\GridOptions;
use Zend\Stdlib\AbstractOptions;
use Zend\View\Helper\AbstractHelper;

class Grid extends AbstractHelper
{
    /**
     * @var array
     */
    protected $attributeMapColumn = array(
        'align'                => 'align',
        'column_attributes'    => 'cellattr',
        'class'                => 'classes',
        'date_format'          => 'datefmt',
        'default_value'        => 'defval',
        'edi_element'          => 'edittype',
        'edit_element_options' => 'formoptions',
        'edit_options'         => 'editoptions',
        'edit_rules'           => 'editrules',
        'format'               => 'formatter',
        'format_options'       => 'formatoptions',
        'is_editable'          => 'isEditable',
        'is_fixed'             => 'page',
        'is_frozen'            => 'frozen',
        'is_hidden'            => 'hidden',
        'is_hideable'          => 'hidedlg',
        'is_searchable'        => 'search',
        'is_sortable'          => 'sortable',
        'is_resizable'         => 'resizable',
        'label'                => 'label',
        'name'                 => 'name',
        'search_element'       => 'stype',
        'search_options'       => 'searchOptions',
        'search_url'           => 'surl',
        'sort_type'            => 'sortType',
        'width'                => 'width',
    );

    /**
     * @var array
     */
    protected $attributeMapGrid = array(
        'alternative_rows'                   => 'altRows',
        'alternative_rows_class'             => 'altclass',
        'auto_encode_incoming_and_post_data' => 'autoencode',
        'autowidth'                          => 'autowidth',
        'caption'                            => 'caption',
        'cell_layout'                        => 'cellLayout',
        'cell_edit'                          => 'cellEdit',
        'cell_edit_url'                      => 'editurl',
        'cell_save_type'                     => 'cellsubmit',
        'cell_save_url'                      => 'cellurl',
        'data_string'                        => 'dataString',
        'data_type'                          => 'dataType',
        'default_page'                       => 'page',
        'default_sort_column'                => 'sortname',
        'default_sort_order'                 => 'sortorder',
        'expand_column_identifier'           => 'ExpandColumn',
        'expand_column_on_click'             => 'ExpandColClick',
        'force_fit'                          => 'forceFit',
        'grid_state'                         => 'gridState',
        'grouping'                           => 'grouping',
        'header_titles'                      => 'headerTitles',
        'height'                             => 'height',
        'hover_rows'                         => 'hoverrows',
        'load_once'                          => 'loadOnce',
        'load_type'                          => 'loadui',
        'multi_select'                       => 'multiselect',
        'multi_select_key'                   => 'multikey',
        'multi_select_width'                 => 'multiselectWidth',
        'pager_element_id'                   => 'pager',
        'pager_position'                     => 'pagerpos',
        'pager_show_buttions'                => 'pgbuttons',
        'pager_show_input'                   => 'pginput',
        'render_footer_row'                  => 'footerrow',
        'render_records_info'                => 'viewrecords',
        'render_row_numbers_column'          => 'rownumbers',
        'request_type'                       => 'mtype',
        'resize_class'                       => 'resizeClass',
        'records_per_page'                   => 'rowNum',
        'records_per_page_list'              => 'rowList',
        'scroll'                             => 'scroll',
        'scroll_offset'                      => 'scrollOffset',
        'scroll_rows'                        => 'scrollRows',
        'scroll_timeout'                     => 'scrollTimeout',
        'shrink_to_fit'                      => 'shrinkToFit',
        'sorting_columns'                    => 'sortable',
        'sorting_columns_definition'         => 'viewsortcols',
        'tree_grid'                          => 'treeGrid',
        'tree_grid_type'                     => 'treeGridModel',
        'tree_grid_icons'                    => 'treeIcons',
        'url'                                => 'url',
        'width'                              => 'width',
    );

    /**
     * @var GridInterface
     */
    protected $grid;

    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  GridInterface|null $grid
     * @return string|Grid
     */
    public function __invoke(GridInterface $grid = null)
    {
        if (!$grid) {
            return $this;
        }

        if(null !== $grid) {
            $this->setGrid($grid);
        }

        return $this->render();
    }

    /**
     * Render grid
     *
     * @throws Exception\UnexpectedValueException
     * @return string
     */
    public function render()
    {
        if (null === $this->grid) {
            throw new Exception\UnexpectedValueException('No instance of LemoGrid\GridInterface given');
        }

        if (!$this->grid instanceof GridInterface) {
            throw new Exception\UnexpectedValueException(sprintf(
                'Expected instance of LemoGrid\GridInterface; received "%s"',
                get_class($this->grid)
            ));
        }

        try {
            $html = array();
            $html[] = $this->renderHtml();
            $html[] = $this->renderScript();

            return implode(PHP_EOL, $html);
        } catch (\Exception $e) {
            ob_clean();
            trigger_error($e->getMessage(), E_USER_WARNING);

            return $e->getMessage();
        }
    }

    /**
     * Render HTML of grid
     *
     * @return string
     */
    protected function renderHtml()
    {
        $html = array();
        $html[] = '<table id="' . $this->getGrid()->getName() . '"></table>';

        // Pokud se nema zobrazit paticka
        if($this->getGrid()->getOptions()->getRenderFooterRow()) {
            $xhtml[] = '<div id="' . $this->getGrid()->getOptions()->getPagerElementId() . '"></div>';
        }

        return implode(PHP_EOL, $html);
    }

    /**
     * Render script of grid
     *
     * @return string
     */
    protected function renderScript()
    {
        $script[] = '<script type="text/javascript">';
        $script[] = '$(document).ready(function(){';
        $script[] = '    $(\'#' . $this->getGrid()->getName() . '\').jqGrid({';
        $script[] = '        ' . $this->renderScriptAttributes('grid', $this->getGrid()->getOptions()) . ', ' . PHP_EOL;
        $script[] = '        colModel: [';

        $columns = $this->getGrid()->getColumns();
        $columnsCount = count($columns) -1;
        foreach($columns as $index => $column) {
            if($index != $columnsCount) { $delimiter = ','; } else { $delimiter = ''; }
            $script[] = '            {' . $this->renderScriptAttributes('column', $column->getAttributes()) . '}' . $delimiter;
        }

        $script[] = '        ]';
        $script[] = '    });';

        $filterToolbar = $this->getGrid()->getOptions()->getFilterToolbar();
        if($filterToolbar['enabled'] == true) {
            if($filterToolbar['stringResult'] == true) { $stringResult = 'true'; } else { $stringResult = 'false'; }
            if($filterToolbar['searchOnEnter'] == true) { $searchOnEnter = 'true'; } else { $searchOnEnter = 'false'; }
            $script[] = '    $(\'#' . $this->getGrid()->getName() . '\').jqGrid(\'filterToolbar\',{stringResult: ' . $stringResult . ', searchOnEnter: ' . $searchOnEnter . '});' . PHP_EOL;
        }

        $script[] = '    $(window).bind(\'resize\', function() {';
        $script[] = '        $(\'#' . $this->getGrid()->getName() . '\').setGridWidth($(\'#gbox_' . $this->getGrid()->getName() . '\').parent().width());';
        $script[] = '    }).trigger(\'resize\');';
        $script[] = '});';
        $script[] = '</script>';

        return implode(PHP_EOL, $script);
    }

    /**
     * Render script of attributes
     *
     * @param  string $type
     * @param  AbstractOptions $attributes
     * @return string
     */
    protected function renderScriptAttributes($type, AbstractOptions $attributes)
    {
        $script = array();

        if('grid' == $type) {
            $attributes = $this->modifyGridAttribute($attributes);
            $separator = ', ' . PHP_EOL;
        }

        // Convert attributes to array
        $attributes = $attributes->toArray();

        foreach($attributes as $key => $value) {
            if(null === $value) {
                continue;
            }

            if('grid' == $type) {
                if(!array_key_exists($key, $this->attributeMapGrid)) {
                    continue;
                }

                $key = $this->convertGridAttributeName($key);
            }

            if('column' == $type) {
                if(!array_key_exists($key, $this->attributeMapColumn)) {
                    continue;
                }

                $key = $this->convertColumnAttributeName($key);
                $separator = ', ';
            }

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
                    } elseif(strtolower($key) == 'treeIcons') {
                        $values[] = $k . ":'" .  $val . "'";
                    } else {
                        $values[] = "'" .  $val . "'";
                    }
                }

                if(strtolower($key) == 'treeIcons') {
                    $script[] = $key . ': {' . implode(',', $values) . '}';
                } else {
                    $script[] = $key . ': [' . implode(',', $values) . ']';
                }
            } elseif(is_numeric($value)) {
                $script[] = $key . ': ' . $value;
            } elseif(is_bool($value)) {
                if($value == true) {
                    $value = 'true';
                } else {
                    $value = 'false';
                }
                $script[] = $key . ': ' . $value;
            } else {
                $script[] = $key . ': \'' . $value . '\'';
            }
        }

        return implode($separator, $script);
    }

    /**
     * Convert attribute name to jqGrid attribute name
     *
     * @param  string $name
     * @return string
     */
    protected function convertColumnAttributeName($name)
    {
        if(array_key_exists($name, $this->attributeMapColumn)) {
            $name = $this->attributeMapColumn[$name];
        }

        return $name;
    }

    /**
     * Convert attribute name to jqGrid attribute name
     *
     * @param  string $name
     * @return string
     */
    protected function convertGridAttributeName($name)
    {
        if(array_key_exists($name, $this->attributeMapGrid)) {
            $name = $this->attributeMapGrid[$name];
        }

        return $name;
    }

    /**
     * Modify grid attributes before rendering
     *
     * @param  GridOptions $attributes
     * @return GridOptions
     */
    protected function modifyGridAttribute(GridOptions $attributes)
    {
        if($this->getGrid()->hasQueryParam('sidx')) {
            $attributes->setDefaultSortColumn($this->getGrid()->getQueryParam('sidx'));
        }
        if($this->getGrid()->hasQueryParam('sord')) {
            $attributes->setDefaultSortColumn($this->getGrid()->getQueryParam('sord'));
        }

        return $attributes;
    }

    /**
     * Set instance of Grid
     *
     * @param  GridInterface $grid
     * @return Grid
     */
    public function setGrid(GridInterface $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Retrieve instance of Grid
     *
     * @throws Exception\UnexpectedValueException
     * @return GridInterface
     */
    public function getGrid()
    {
        return $this->grid;
    }
}
