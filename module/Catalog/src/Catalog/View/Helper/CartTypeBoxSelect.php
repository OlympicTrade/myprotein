<?php
namespace Catalog\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CartTypeBoxSelect extends AbstractHelper
{
    public function __invoke($name, $props, $propsName, $cVal = '')
    {
        $options = [];
        $optsLen = 0;
        foreach($props as $size) {
            $options[$size->getId()] = $size->get('name');
            $optsLen += mb_strlen($size->get('name'));
        }

        if(count($options) == 1) {
            $fk = key($options);

            if(!$options[$fk]) {
                return '<input type="hidden" name="' . $name . '" value="' . key($options) . '">';
            }
        }

            $html =
            '<div class="row">'
                .'<div class="label">' . $propsName . '</div>';

        $colors = [
            'Черный'     => '#000000',
            'Белый'      => '#ffffff',
            'Синий'      => '#0060c1',
            'Голубой'    => '#67aef5',
            'Фиолетовый' => '#c100b6',
            'Розовый'    => '#f865f0',
            'Красный'    => '#ff0000',
            'Алый'       => '#fd8989',
            'Коричневый' => '#b16800',
            'Светло-коричневый' => '#de8e1d',
            'Оранжевый'  => '#ff7800',
            'Желтый'     => '#ffea00',
            'Зеленый'    => '#6dae04',
            'Cветло-зелёный' => '#ade159',
        ];

        if($propsName == 'Цвет') {
            $html .=
                '<div class="select-group color">';

            $defVal = '';
            foreach($options as $key => $val) {
                if($cVal == $key) {
                    $defVal = $key;
                    $selected = ' selected';
                } else {
                    $selected = '';
                }

                $html .=
                    '<span data-value="' . $key . '" style="background-color: ' . $colors[$val] . '" title="' . $val . '"' . $selected . '></span>';
            }
            $html .=
                    '<input type="hidden" name="' . $name . '" value="' . $defVal . '">'
                .'</div>';
        } elseif($optsLen > 30) {
            $html .=
                '<div class="select-group">'
                .'<select class="std-select" name="' . $name . '">';
            foreach($options as $key => $val) {
                $html .=
                    '<option value="' . $key . '"' . ($cVal == $key ? ' selected' : '') . '>' . $val . '</option>';;
            }
            $html .=
                '</select>'
                .'</div>';
        } else {
            $html .=
                '<div class="select-group">';

            $defVal = '';
            foreach($options as $key => $val) {
                if($cVal == $key) {
                    $defVal = $key;
                    $selected = ' selected';
                } else {
                    $selected = '';
                }

                $html .=
                    '<span data-value="' . $key . '"' . $selected . '>' . $val . '</span>';
            }
            $html .=
                    '<input type="hidden" name="' . $name . '" value="' . $defVal . '">'
                .'</div>';
        }

        $html .=
            '</div>';
        
        return $html;
    }
}