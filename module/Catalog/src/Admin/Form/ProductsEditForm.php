<?php
namespace CatalogAdmin\Form;

use Aptero\Form\Admin\Form;

use BlogAdmin\Model\Article;
use CatalogAdmin\Model\CatalogTypes;
use CatalogAdmin\Model\Products;
use Zend\Db\Sql\Expression;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class ProductsEditForm extends Form
{
    public function setModel($model)
    {
        parent::setModel($model);

        $this->get('parent')->setOption('model', $this->getModel());

        $this->get('catalog_id')->setOptions(array(
            'model' => $model->getPlugin('catalog')
        ));

        $this->get('brand_id')->setOptions(array(
            'model' => $model->getPlugin('brand')
        ));

        $this->get('image-image')->setOptions(array(
            'model' => $model->getPlugin('image'),
        ));

        $this->get('images-images')->setOptions([
            'model'   => $model->getPlugin('images'),
            'product' => $model,
        ]);

        $this->get('types-collection')->setOption('model', $model->getPlugin('types'));
        $this->get('size-collection')->setOption('model', $model->getPlugin('size'));
        $this->get('features-collection')->setOption('model', $model->getPlugin('features'));
        $this->get('taste-collection')->setOption('model', $model->getPlugin('taste'));
        $this->get('composition-collection')->setOption('model', $model->getPlugin('composition'));
        $this->get('articles-collection')->setOption('model', $model->getPlugin('articles'));

        $recommendedModel = $model->getPlugin('recommended');
        /*$recommendedModel->select()
            ->join(['p' => 'products'], 'p.id = t.product_id', ['id', 'product-name' => 'name'])
            ->order('name');*/
        
        $this->get('recommended-collection')->setOption('model', $recommendedModel);
    }

    public function __construct()
    {
        parent::__construct('edit-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('autocomplete', 'off');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
        ));

        $this->add(array(
            'name' => 'parent',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Родительский товар',
                'empty'   => '',
                'sort'    => 'Name',
            ),
        ));

        $types = CatalogTypes::getEntityCollection();
        $types->select()
            ->columns(['id', 'name' => new Expression('CONCAT(c.name, " - ", t.name)')])
            ->join(['c' => 'catalog'], 't.depend = c.id', [])
            ->order('c.name, t.name');

        $this->add([
            'name' => 'types-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'      => [
                    'type_id' => [
                        'label'   => 'Категории',
                        'width'   => 200,
                        'options' => $types
                    ],
                ]
            ],
        ]);

        $this->add(array(
            'name' => 'articles-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => [
                'options'      => [
                    'article_id' => [
                        'label'   => 'Статьи',
                        'width'   => 150,
                        'options' => new Article()
                    ],
                ]
            ],
        ));

        $this->add(array(
            'name' => 'recommended-collection',
            'type' => 'Aptero\Form\Element\Admin\Collection',
            'options' => array(
                'options' => [
                    'product_id' => [
                        'label'   => 'Рекомендованные товары',
                        'width'   => 150,
                        'sort'    => 'name',
                        'options' => new Products()
                    ],
                ]
            ),
        ));

        $this->add(array(
            'name' => 'name',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Название',
            ),
        ));

        $this->add(array(
            'name' => 'tags',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Теги',
            ),
        ));

        $this->add(array(
            'name' => 'discount',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Скидка в процентах',
            ),
        ));

        $this->add(array(
            'name' => 'prop_name_1',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Название свойства',
            ),
            'attributes'=> array(
                'placeholder' => 'Размер',
            ),
        ));

        $this->add(array(
            'name' => 'prop_name_2',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Название свойства',
            ),
            'attributes'=> array(
                'placeholder' => 'Вкус',
            ),
        ));

        $this->add(array(
            'name' => 'margin',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Наценка в процентах',
            ),
        ));

        $this->add(array(
            'name' => 'type',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Тип',
            ),
        ));

        $this->add(array(
            'name' => 'url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Url',
                'help'  => 'Используется как ЧПУ. По умолчанию заполняется транслитом на основании названия страницы'
            ),
        ));

        $this->add(array(
            'name' => 'mp_url',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Myprotein Url',
            ),
        ));

        $this->add(array(
            'name' => 'catalog_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Категория'
            ),
        ));

        $this->add(array(
            'name' => 'brand_id',
            'type'  => 'Aptero\Form\Element\TreeSelect',
            'options' => array(
                'label'   => 'Производитель'
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Заголовок (Title)'
            ),
        ));

        $this->add(array(
            'name' => 'preview',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Описание под название в карточке'
            ),
        ));

        $this->add(array(
            'name' => 'desc',
            'type'  => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Описание под название в списке'
            ),
        ));

        $this->add(array(
            'name' => 'video',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'YouTube'
            ),
        ));


        $form = $this;
        $addTabs = function($prefix) use ($form) {
            $this->addMeta($prefix);
            $form->add([
                'name' => $prefix . 'url',
                'type'  => 'Zend\Form\Element\Text',
                'options' => [
                    'label' => 'URL'
                ],
            ]);

            $form->add([
                'name' => $prefix . 'header',
                'type'  => 'Zend\Form\Element\Text',
                'options' => [
                    'label' => 'Заголовок'
                ],
            ]);

            $form->add([
                'name' => $prefix . 'text',
                'type'  => 'Zend\Form\Element\Textarea',
                'attributes'=> [
                    'class' => 'editor',
                    'id'    => 'page-text'
                ],
            ]);
        };

        $addTabs('attrs-tab1_');
        $addTabs('attrs-tab2_');
        $addTabs('attrs-tab3_');

        $this->add(array(
            'name' => 'keywords',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Ключевые слова (Keywords)'
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'type'  => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Описание (Description)'
            ),
        ));

        $this->add(array(
            'name' => 'image-image',
            'type'  => 'Aptero\Form\Element\Admin\Image',
            'options' => array(),
        ));

        $this->add(array(
            'name' => 'certificate-file',
            'type'  => 'Aptero\Form\Element\Admin\File',
            'options' => array(),
        ));

        $this->add(array(
            'name' => 'instruction-file',
            'type'  => 'Aptero\Form\Element\Admin\File',
            'options' => array(),
        ));

        $this->add([
            'name' => 'images-images',
            'type'  => 'Aptero\Form\Element\Admin\ProductImages',
            'options' => [],
        ]);

        $this->add(array(
            'name' => 'text',
            'type'  => 'Zend\Form\Element\Textarea',
            'attributes'=> array(
                'class' => 'editor',
                'id'    => 'page-text'
            ),
        ));

        $this->add([
            'name' => 'ya_market',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [
                    0 => 'Не выгружать',
                    1 => 'Выгружать',
                ],
                'label' => 'Яндекс Market',
            ],
        ]);

        $this->add([
            'name' => 'go_merchant',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'options' => [
                    1 => 'Выгружать',
                    0 => 'Не выгружать',
                ],
                'label' => 'Google Merchant',
            ],
        ]);

        $this->add(array(
            'name' => 'sort',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    0 => 'Очень низкий',
                    1 => 'Низкий',
                    2 => 'Средне',
                    3 => 'Высокий',
                    4 => 'Очень высокий',
                ),
                'label' => 'Приоритет закупки',
            ),
        ));

        $this->add(array(
            'name' => 'vegan',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'options' => array(
                    0 => 'Без особенностей',
                    1 => 'Для вегетарианцев',
                    2 => 'Для тупых веганов',
                ),
                'label' => 'Вегетарианство',
            ),
        ));

        $this->add(array(
            'name' => 'composition-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => array(
                'options'      => array(
                    'name'     => array('label' => 'Название', 'width' => 150),
                    'portion'  => 'Порция',
                    'percent'  => '100г',
                    'type'     =>  array('label' => 'Тип (1 заг. 0 стр. 3 подстр.)', 'width' => 150),
                    'sort'     => 'Сортировка',
                )
            ),
        ));

        $this->add(array(
            'name' => 'features-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => array(
                'options'      => array(
                    'name'     => array('label' => 'Особенность', 'width' => 250),
                )
            ),
        ));

        $this->add(array(
            'name' => 'taste-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => array(
                'options'      => array(
                    'name'     => array('label' => 'Вкус', 'width' => 150),
                    'coefficient'   => 'Коэфициент',
                )
            ),
        ));

        $this->add(array(
            'name' => 'size-collection',
            'type'  => 'Aptero\Form\Element\Admin\Collection',
            'options' => array(
                'options'      => array(
                    'name'     => array('label' => 'Вес/Размер', 'width' => 150),
                    'price'    => 'Стоимость',
                )
            ),
        ));
    }

    public function setFilters()
    {
        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'name',
            'required' => true,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'url',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'mp_url',
            'required' => false,
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        )));

        $this->setInputFilter($inputFilter);
    }
}