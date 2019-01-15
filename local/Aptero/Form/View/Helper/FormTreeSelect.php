<?php
namespace Aptero\Form\View\Helper;

use Aptero\Db\Entity\Entity;
use Aptero\Db\Entity\EntityCollectionHierarchy;
use Aptero\Db\Entity\EntityHierarchy;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\AbstractHelper;

class FormTreeSelect extends AbstractHelper
{
    /**
     * @var ElementInterface
     */
    protected $element = null;

    /**
     * @var array
     */
    protected $validSelectAttributes = array(
        'name'      => true,
        'autofocus' => true,
        'disabled'  => true,
        'form'      => true,
        'multiple'  => true,
        'required'  => true,
        'size'      => true
    );

    /**
     * @var array
     */
    protected $validOptionAttributes = array(
        'disabled' => true,
        'selected' => true,
        'label'    => true,
        'value'    => true,
    );

    protected $field = 'name';

    /**
     * @param ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $this->element = $element;

        $attributes = $element->getAttributes();
        $value      = $element->getValue();

        $this->validTagAttributes = $this->validSelectAttributes;
        $html = '<select ' . $this->createAttributesString($attributes) .  '>';

        $model = $element->getOption('model');
        $catalog = $element->getOption('collection');

        if($catalog instanceof Entity) {
            $catalog = $catalog->getCollection();
        }

        if(!$catalog) {
            $catalog = $model->getCollection();
        }

        if($element->getOption('sort') !== null) {
            $catalog->select()->order($element->getOption('sort'));
        }

        $thisId = $model ? $model->getId() : 0;

        if($catalog instanceof EntityCollectionHierarchy) {
            $catalog->setParentId(0);
        }

        if($element->getOption('empty') !== null) {
            $html .= '<option value="">' . $element->getOption('empty') . '</option>';
        }

        if($element->getOption('field') !== null) {
            $this->field = $element->getOption('field');
        }

        $this->validTagAttributes = $this->validOptionAttributes;
        $html .= $this->renderOptions($thisId, $value, $catalog);

        $html .= '</select>';

        return $html;
    }

    /**
     * @param $thisId
     * @param $value
     * @param $catalog
     * @param string $prefix
     * @return string
     */
    public function renderOptions($thisId, $value, $catalog, $prefix = '')
    {
        $html = '';
        $newPrefix = '&mdash;' . $prefix;

        foreach($catalog as $row) {
            $attributes = [];

            if($row->getId() == $value) {
                $attributes['selected'] = true;
            }

            /*if($row->getId() == $thisId) {
                $attributes['disabled'] = true;
            }*/

            $html .=
                '<option'
                .' value="' . $row->getId() . '"' . $this->createAttributesString($attributes)
                //. ($disableHierarchyValues && $hasChildren ? ' disabled' : '')
                . '>' . $prefix . ' ' . $row->get($this->field) . '</option>';


            if($row instanceof EntityHierarchy) {
                $children = $row->getChildren();

                if($children->count()) {
                    $html .= $this->renderOptions($thisId, $value, $children, $newPrefix);
                }
            }
        }

        return $html;
    }

    /**
     * @param ElementInterface $element
     * @return $this|string
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }
}