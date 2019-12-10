<?php
namespace Aptero\Service\Admin;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityCollection;
use Aptero\Db\Entity\EntityCollectionHierarchy;
use Aptero\Service\AbstractService;
use Zend\Paginator\Paginator;

class TableService extends AbstractService
{
    const FIELD_TYPE_TEXT   = 1;
    const FIELD_TYPE_PRICE  = 2;
    const FIELD_TYPE_BOOL   = 3;
    const FIELD_TYPE_EMAIL  = 4;
    const FIELD_TYPE_DATE   = 5;
    const FIELD_TYPE_LINK   = 6;
    const FIELD_TYPE_NUMBER = 7;
    const FIELD_TYPE_IMAGE  = 8;
    const FIELD_TYPE_DATETIME = 9;
    const FIELD_TYPE_CHECKBOX = 10;
    const FIELD_TYPE_INPUT    = 11;
    const FIELD_TYPE_TEXTAREA = 12;
    const FIELD_TYPE_SELECT   = 13;

    /**
     * @var Entity
     */
    protected $model = null;

    /**
     * @var string
     */
    protected $moduleName = '';

    /**
     * @var string
     */
    protected $sectionName = '';

    public function __construct($model = null)
    {
        if($model) {
            $this->setModel($model);
        }
    }

    /**
     * @param array $formData
     * @param Entity $model
     * @return bool
     */
    public function save($formData, $model)
    {
        $model->unserializeArray($formData);
        $model->save();

        return true;
    }

    public function getAutoComplete($filters)
    {
        $result = [];

        if(!$filters['search']) {
            return $result;
        }

        $model = $this->getModel();

        $list = $model->getCollection();

        $list->select()
            ->limit(10);

        $list = $this->setFilter($list, $filters);

        foreach ($list as $item) {
            $result[] = [
                'label' => ($model->hasProperty('name') ? $item['name'] : $item['id']),
                'url'   => str_replace('/admin/', '/admin/mobile/', $item->getEditUrl()),
            ];
        }

        return$result;
    }

    /**
     * @param string $sort
     * @param string $direct
     * @param array $filters
     * @param int $parentId
     * @return Paginator
     */
    public function getList($sort = '', $direct = '', $filters = [], $parentId = 0)
    {
        $collection = $this->getListBaseSelect();

        if($collection instanceof EntityCollectionHierarchy) {
            $collection->setParentId($parentId);
            $collection->select()->where(['parent' => 0]);
        }

        $collection = $this->setFilter($collection, $filters);
        $collection = $this->setListOrder($collection, $sort, $direct);

        return $collection->getPaginator();
    }

    /**
     * @param EntityCollection $list
     * @param $filters
     * @return EntityCollection
     */
    public function setFilter($list, $filters)
    {
        $model = $list->getPrototype();

        if($filters['search']) {
            $list = $this->setSearchFilter($list, $filters['search']);
            unset($filters['search']);
        }

        foreach($_GET as $key => $value) {
            if($value && $model->hasProperty($key)) {
                $list->select()->where(array($key => $value));
            }
        }

        foreach($filters as $field => $val) {
            if(!empty($val)) {
                $list->select()->where(array($field => $val));
            }
        }

        if($model->hasProperty('sort')) {
            $list->select()->order('sort');
        } else {
            $list->select()->order('id DESC');
        }

        $list->select()->group('t.id');

        return $list;
    }

    /**
     * @param Entity $model
     * @param EntityCollection $list
     * @param $query
     * @return mixed
     */
    public function setSearchFilter($list, $query)
    {
        $model = $list->getPrototype();


        if($model->hasProperty('name')) {
            $list->select()->where
                ->nest()
                    ->like('id', '%' . $query . '%')
                    ->or
                    ->like('name', '%' . $query . '%')
                ->unnest();
        } else {
            $list->select()->where
                ->like('id', '%' . $query . '%');
        }

        return $list;
    }

    /**
     * @param $collection
     * @param string $sort
     * @param string $direct
     * @return \Aptero\Db\Entity\EntityCollection
     */
    public function setListOrder($collection, $sort, $direct)
    {
        $sort = $sort ? $sort : 'id';
        $direct = $direct ? $direct : 'up';

        $sort .= $direct == 'down' ? ' ASC' : ' DESC';

        $collection->select()->order($sort);
        
        return $collection;
    }

    /**
     * @return EntityCollection
     */
    public function getListBaseSelect()
    {
        return $this->getModel()->getCollection();
    }

    /**
     * @param array $sortList
     */
    public function updateSort($sortList)
    {
        foreach($sortList as $sort) {
            if(!empty($sort['id'])) {
                $update = $this->getModel()->update();

                $update->where(array('id' => $sort['id']));
                $update->set(array('sort' => $sort['sort']));

                $this->getModel()->execute($update);
            }
        }
    }

    /**
     * @param int $id
     * @param string $field
     * @param bool $val
     */
    public function fieldSwitch($id, $field, $val)
    {
        $update = $this->getModel()->update();

        $update->where(array('id' => $id));
        $update->set(array($field => $val));

        $this->getModel()->execute($update);
    }

    /**
     * @param Entity $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return Entity
     */
    public function getModel()
    {
        if(!$this->model) {
            $this->model = $this->getDefaultModel();
        }

        return $this->model;
    }

    public function getDefaultModel()
    {
        $modelClassName = '\\' . ucfirst($this->moduleName) . 'Admin\\Model\\' . ucfirst($this->sectionName);

        return new $modelClassName();
    }

    /**
     * @return \Aptero\Form\Form
     */
    public function getEditForm()
    {
        $formClassName = '\\' . ucfirst($this->moduleName) . 'Admin\\Form\\' . ucfirst($this->sectionName) . 'EditForm';

        return new $formClassName();
    }

    /**
     * @return \Aptero\Form\Form
     */
    public function getFilterForm()
    {
        $formClassName = '\\' . ucfirst($this->moduleName) . 'Admin\\Form\\' . ucfirst($this->sectionName) . 'FilterForm';

        return new $formClassName();
    }

    /**
     * @return \Aptero\Form\Form
     */
    public function getSettingsForm()
    {
        $formClassName = '\\' . ucfirst($this->moduleName) . 'Admin\\Form\\' . ucfirst($this->sectionName) . 'SettingsForm';

        return new $formClassName();
    }

    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
        return $this;
    }

    public function setSectionName($sectionName)
    {
        $this->sectionName = $sectionName;
        return $this;
    }
}