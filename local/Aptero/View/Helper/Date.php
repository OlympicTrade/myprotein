<?php
namespace Aptero\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Aptero\String\Date as StDate;

class Date extends AbstractHelper
{
    protected $months = array(
        1	=> 'January',
        2	=> 'February',
        3	=> 'March',
        4	=> 'April',
        5	=> 'May',
        6	=> 'June',
        7	=> 'July',
        8	=> 'August',
        9	=> 'September',
        10	=> 'October',
        11	=> 'November',
        12	=> 'December'
    );

    public function __invoke($date, $options = [], $pattern = 'Y-m-d H:i:s')
    {
        $options = array_merge(array(
            'day'    => true,
            'month'  => true,
            'year'   => true,
            'time'   => false,
        ), $options);

        if($date instanceof \DateTime) {
            $dt = $date;
        } elseif($dt = \DateTime::createFromFormat($pattern, $date)) {

        } elseif ($dt = \DateTime::createFromFormat('Y-m-d', $date)) {

        } else {
            return '';
        }

        $str = '';

        if($options['time']) {
            $str .= '<span>' . $dt->format('H') . ':' . $dt->format('i') . '</span> ';
        }

        if($options['day']) {
            $str .= $dt->format('d');
        }

        if($options['month']) {
            if($options['month'] == 'short') {
                $str .= ' ' . $this->getView()->translate(StDate::$monthsShort[$dt->format('n')]);
            } else {
                $str .= ' ' . $this->getView()->translate(StDate::months[$dt->format('n')]);
            }
        }

        if($options['year']) {
            $str .= ' ' . $dt->format('Y');
        }

        return $str;
    }
}