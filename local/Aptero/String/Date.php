<?php
namespace Aptero\String;

class Date
{
    static public $months = array(
        1	=> 'Январь',
        2	=> 'Февраль',
        3	=> 'Март',
        4	=> 'Апрель',
        5	=> 'Май',
        6	=> 'Июнь',
        7	=> 'Июль',
        8	=> 'Август',
        9	=> 'Сентябрь',
        10	=> 'Октябрь',
        11	=> 'Ноябрь',
        12	=> 'Декабрь'
    );

    static public $months2 = array(
        1	=> 'Января',
        2	=> 'Февраля',
        3	=> 'Марта',
        4	=> 'Апреля',
        5	=> 'Мая',
        6	=> 'Июня',
        7	=> 'Июля',
        8	=> 'Августа',
        9	=> 'Сентября',
        10	=> 'Октября',
        11	=> 'Ноября',
        12	=> 'Декабря'
    );

    static public $monthsShort = array(
        1	=> 'Янв',
        2	=> 'Фев',
        3	=> 'Мар',
        4	=> 'Апр',
        5	=> 'Маq',
        6	=> 'Июн',
        7	=> 'Июл',
        8	=> 'Авг',
        9	=> 'Сен',
        10	=> 'Окт',
        11	=> 'Ноя',
        12	=> 'Дек'
    );

    static public $weekDays = array(
        1   => 'Понедельник',
        2   => 'Вторник',
        3   => 'Среда',
        4   => 'Четверг',
        5   => 'Пятница',
        6   => 'Суббота',
        7   => 'Воскресенье',
    );

    /**
     * @var \DateTime
     */
    protected $dt;

    public function __construct($date = '')
    {
        if($date) {
            $this->setDate($date);
        }

        return $this;
    }

    public function setDate($date)
    {
        if($date instanceof \DateTime) {
            $this->dt = $date;
        } else {
            $this->dt = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        }

        return $this;
    }

    static public function getYears($from = -6, $to = 6)
    {
        $years = [];
        $cYear = date('Y');
        for($year = $cYear + $from; $year < $cYear + $to; $year++) {
            $years[$year] = $year;
        }

        return $years;
    }

    static public function getMonths()
    {
        return self::$months;
    }

    public function getMonth()
    {
        return self::$months[$this->dt->format('m')];
    }

    public function getWeekDay()
    {
        return self::$weekDays[$this->dt->format('N')];
    }

    /**
     * @param $str
     * @return \DateTime
     */
    static public function strToDateTime($str)
    {
        if(!$str) {
            $str = '0000-00-00';
        }

        $time = \DateTime::createFromFormat('d.m.Y', $str);
        if(!$time) {
            $time = \DateTime::createFromFormat('Y-m-d', $str);
        }

        return $time;
    }

    /**
     * @param $str
     * @return \DateTime
     */
    static public function strToTime($str)
    {
        $str = str_replace('.', ':', $str);

        if(!$str) {
            $str = '00:00:00';
        }

        $time = \DateTime::createFromFormat('H:i:s', $str);
        if(!$time) {
            $time = \DateTime::createFromFormat('H:i', $str);
            if(!$time) {
                $time = \DateTime::createFromFormat('H', $str);
            }
        }

        return $time;
    }

    public function toStr($options = [])
    {
        $options = array_merge([
            'day'    => true,
            'month'  => true,
            'year'   => true,
            'time'   => false,
        ], $options);

        if(!$this->dt) {
            return '';
        }

        $str = '';

        if($options['day']) {
            $str .= $this->dt->format('d');
        }

        if($options['month']) {
            if($options['month'] == 'short') {
                $str .= ' ' . self::$monthsShort[$this->dt->format('n')];
            } else {
                $str .= ' ' . self::$months[$this->dt->format('n')];
            }
        }

        if($options['year']) {
            $str .= ' ' . $this->dt->format('Y');
        }

        if($options['time']) {
            $str .= ' <span>' . $this->dt->format('H') . ':' . $this->dt->format('i') . '</span> ';
        }

        return $str;
    }
}