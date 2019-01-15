<?php
namespace Aptero\View\Helper\Admin;

use Aptero\Db\Entity\EntityCollectionHierarchy;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;
use Aptero\Service\Admin\TableService;

use Aptero\Db\Entity\EntityHierarchy;

class TableList extends AbstractHelper
{
    protected $fields = null;
    protected $options = null;

    public function __invoke($fields, $tableData, $options = array())
    {
        $this->setOptions($options);
        $this->setFields($fields);

        if(!$tableData->count()) {
            return '<div class="table-list-empty">Записей не найдено</div>';
        }

        $view = $this->getView();

        $tableId = rand(0, 100000);

        $html =
            '<div class="table-list" data-id="' . $tableId . '" data-module="' . $view->module->get('module') . '" data-section="' . $view->module->get('section') . '">';

        $html .= $this->tableHeader();

        $html .=
            '<ul class="rowset">';

        foreach($tableData as $row) {
            $html .= $this->tableRows($row);
        }

        $html .=
            '</ul>';

        $html .=
            '<div class="popup-delete" style="display: none;">'
                .'<div style="text-align: center; margin-bottom: 10px;">Удалить запись?</div>'
                .'<a href="#" class="btn btn-green yes"><i class="fa fa-check"></i> Удалить</a> '
                .'<a href="" class="btn btn-red no"><i class="fa fa-times"></i> Отмена</a>'
            .'</div>';

        $html .=
            '</div>';

        if($tableData instanceof Paginator) {
            $html .=
                $this->getView()->paginationControl($tableData, 'Sliding', 'admin-pagination-slide', array('route' => 'application/pagination'));
        }

        $html .=
            '<script>$(function(){dataTableAction("' . $tableId . '", "' . $this->options['module'] . '", "' . $this->options['section'] . '");});</script>';

        return $html;
    }

    protected function tableHeader()
    {
        $html =
            '<div class="header">';

        $html .=
            '<div class="cells-fields">';

        foreach($this->fields as $field => $info) {
            $url = $this->sortUri($info['field']);

            $cellStyle = '';
            if(!empty($info['thStyle']) && is_array($info['thStyle'])) {
                foreach($info['thStyle'] as $param => $style) {
                    $cellStyle = $param . ': ' . $style;
                }
            }

            $arr = '';

            if($info['type'] == TableService::FIELD_TYPE_CHECKBOX) {
                $html .=
                    '<span class="tb-cell" style="width: ' . $info['width'] . '%; ' . $cellStyle . '">'
                        .'<input type="checkbox" class="tb-header-cb">'
                    .'</span>';
                continue;
            }

            if($info['sort']['enabled']){
                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
                $direct = isset($_GET['direct']) ? $_GET['direct'] : 'down';

                if($info['field'] == $sort) {
                    if($direct == 'down') {
                        $arr = ' <i class="fa fa-angle-down direct"></i>';
                    } else {
                        $arr = ' <i class="fa fa-angle-up direct"></i>';
                    }
                }
                $sortLink = '<a href="' . $url . '">' . $info['name'] . '</a>' . $arr;
            } else {
                $sortLink = $info['name'];
            }

            $html .=
                '<span class="tb-cell" style="width: ' . $info['width'] . '%; ' . $cellStyle . '">'
                    .$sortLink
                .'</span>';
        }

        $html .=
            '</div>';

        $html .=
			'<div class="tb-cell cell-action">Действия</div>';

        $html .=
            '</div>';

        return $html;
    }

    protected function tableRows($row, $depth = 0)
    {
        $view = $this->getView();

        if($row instanceof EntityHierarchy) {
            $parentId = $row->getParentId();
        } else {
            $parentId = 0;
        }

        $html =
            '<li data-id="' . $row->getId() . '" data-sort="' . $row->get('sort') . '">'
            .'<div data-id="' . $row->getId() . '" data-parent="' . $parentId . '" class="tb-row">';

        $html .=
            '<div class="cells-fields">';

        foreach($this->fields as $field) {
            $html .= $this->tableCell($row, $field, $depth);
        }

        $html .= '</div>';

        $editUrl = $view->url('admin',
            array('module' => $this->options['module'], 'section' => $this->options['section'], 'action' => 'edit'),
            array('query' => array('id' => $row->getId())));

        $html .=
            '<div class="tb-cell cell-action action">'
                .call_user_func_array($this->options['buttons']['edit'], array($row, $this->getView()))
                .' '
                .call_user_func_array($this->options['buttons']['delete'], array($row, $this->getView()))
            .'</div>';

        $html .=
                '<div class="clear"></div>'
            .'</div>';

        if($row instanceof EntityHierarchy) {

            $children = $row->getChildren();
            $depth++;

            if($children->count()) {
                foreach($children as $child) {
                    $html .= $this->tableRows($child, $depth);
                }
            }
        }

        $html .=
            '</li>';

        return $html;
    }

    /**
     * @param \Aptero\Db\Entity\Entity $row
     * @param string $field
     * @param int $depth
     * @return string
     */
    protected function tableCell($row, $field, $depth) {
        $html = '';

        $varPath = explode('-', $field['field']);
        $varName = array_pop($varPath);

        if(!empty($varPath)) {
            $plugin = $row;
            foreach($varPath as $pluginName) {
                $plugin = $plugin->getPlugin($pluginName);
            }
            $value = $plugin->get($varName);
        } else {
            $value = $row->get($varName);
        }

        if($field['filter']) {
            $value = call_user_func_array($field['filter'], array($value, $row, $this->getView()));
        }

        $view = $this->getView();

        switch($field['type']) {
            case TableService::FIELD_TYPE_PRICE:
                $tdValue = $view->adminPrice($value);
                $tdClass = 'type-text';
                break;
            case TableService::FIELD_TYPE_DATE:
                $tdValue = $view->date($value);
                $tdClass = 'type-date';
                break;
            case TableService::FIELD_TYPE_DATETIME:
                $tdValue = $view->date($value, array('time' => true));
                $tdClass = 'type-date';
                break;
            case TableService::FIELD_TYPE_BOOL:
                $tdValue = $value;
                $tdClass = 'type-bool';
                break;
            case TableService::FIELD_TYPE_NUMBER:
                $tdValue = $view->price($value);
                $tdClass = 'type-text';
                break;
            case TableService::FIELD_TYPE_IMAGE:
                $tdValue = '<img src="' . $value . '">';
                $tdClass = 'type-img';
                break;
            case TableService::FIELD_TYPE_TEXT:
                $tdValue = $value;
                $tdClass = 'type-text';
                break;
            case TableService::FIELD_TYPE_CHECKBOX:
                $tdValue = '<input type="checkbox" class="tb-cb" value="' . $value . '">';
                $tdClass = 'type-cb';
                break;
            case TableService::FIELD_TYPE_LINK:
                $url = $view->url('admin',
                    array(
                        'module'  => $this->options['module'],
                        'section' => $this->options['section'],
                        'action'  => 'edit'),
                    array('query' => array('id' => $row->getId())));

                $tdValue = '<a href="' . $url . '">' . $value . '</a>';
                $tdClass = 'type-text';
                break;
            case TableService::FIELD_TYPE_TEXTAREA:
                $select = new Textarea($field['field'], []);
                $select->setValue($value);
                $tdValue = $view->formElement($select);
                $tdClass = 'type-text';
                break;
            case TableService::FIELD_TYPE_INPUT:
                $select = new Text($field['field'], []);
                $select->setValue($value);
                $tdValue = $view->formElement($select);
                $tdClass = 'type-text';
                break;
            case TableService::FIELD_TYPE_SELECT:
                $select = new Select($field['field'], [
                    'options' => $field['options']
                ]);
                $select->setValue($value);
                $tdValue = $view->formElement($select);
                $tdClass = 'type-text';
                break;
            default:
                $tdValue = strip_tags($value);
                $tdClass = 'type-text';
        }

        $cellStyle = '';
        if(!empty($field['tdStyle']) && is_array($field['tdStyle'])) {
            foreach($field['tdStyle'] as $param => $style) {
                $cellStyle .= ($cellStyle ? '; ' : '') . $param . ': ' . $style;
            }
        }

        if(isset($field['hierarchy']) && $field['hierarchy'] && $depth) {
            $tdValue =  '<div class="branch" style="width: ' . (($depth * 25)) . 'px"></div>' . $tdValue;
        }

        if(isset($field['link']) && !empty($field['link'])) {
            $html .= '<a href="' . call_user_func_array($field['link'], array($row)) . '" ';
        } else {
            $html .= '<div ';
        }

        if(isset($field['tdTitle']) && $field['tdTitle'] === true) {
            $title = ' title="' . str_replace(['"'], [''], $value) . '"';
        } else {
            $title = '';
        }

        $html .=
            'class="tb-cell ' . $tdClass . '" data-field="' . $field['field'] . '" style="width:' . $field['width'] . '%; ' . $cellStyle . '" ' . $title . '>'
                . $tdValue;

        if(isset($field['link']) && !empty($field['link'])) {
            $html .= '</a>';
        } else {
            $html .= '</div>';
        }

        return $html;
    }

    protected function attrsToHtml($attrs)
    {
        if(empty($attrs)) {
            return '';
        }

        $html = '';

        foreach($attrs as $key => $val) {
            $html .= ' ' . $key .'="' . $val . '"';
        }

        return $html;
    }

    protected function sortUri($field)
    {
        if(is_array($field)) {
            $field = $field[0];
        }

        if(isset($_GET['sort']) && $_GET['sort'] == $field) {
            if(isset($_GET['direct']) && $_GET['direct'] == 'down') {
                $direct = 'up';
            } else {
                $direct = 'down';
            }
        } else {
            $direct = 'up';
        }

        if(is_array($field)) {
            $field = implode(',', $field);
        }

        $url = \Aptero\Url\Url::getUrl(array('sort' => $field, 'direct' => $direct));

        return $url;
    }

    protected function setOptions($options) {
        $default = array(
            'module'    => $this->getView()->engine->module->get('module'),
            'section'   => $this->getView()->engine->module->get('section'),
        );

        $options = array_replace_recursive($default, $options);

        $default = array(
            'buttons'   => array(
                'edit'    => function($model, $view) use ($options){
                    $editUrl = $view->url('admin',
                        array(
                            'module' => $options['module'],
                            'section' => $options['section'],
                            'action' => 'edit'),
                        array('query' => array('id' => $model->getId()))
                    );
                    return '<a class="btn btn-green edit" href="' . $editUrl . '"><i class="fa fa-pencil-square-o"></i> Просмотр</a>';
                },
                'delete'  => function($model, $view){
                    return
                        '<a class="btn btn-red tbl-btn-remove trash" data-id="' . $model->getId() . '">'
                            .'<i class="fa fa-trash-o"></i> Удалить'
                        .'</a>';
                }
            ),
        );

        $this->options = array_replace_recursive($default, $options);
    }

    protected function setFields($fields) {
        $default = array(
            'name'      => '',
            'type'      => TableService::FIELD_TYPE_TEXT,
            'field'     => 'name',
            'filter'    => null,
            'link'      => null,
            'width'     => '100',
            'tdStyle'   => array(
                'text-align' => 'left'
            ),
            'thStyle'   => array(
                'text-align' => 'left'
            ),
            'sort'      => array(
                'enabled'   => true
            )
        );

        $this->fields = array();
        foreach($fields as $fieldName => $field) {
            if(!$field['field']) {
                $field['field'] = $fieldName;
            }

            @$this->fields[] = array_merge($default, $field);
        }
    }
}