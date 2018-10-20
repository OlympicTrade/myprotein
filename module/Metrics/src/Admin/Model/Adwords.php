<?php
namespace MetricsAdmin\Model;

use Aptero\Db\Entity\Entity;

class Adwords extends Entity
{
    static public $types = [
        'cpc'      => 'cpc',
        'email'    => 'E-mail',
        'banner'   => 'Баннер',
        'organic'  => 'Поиск',
        'referral' => 'Ссылка',
        'sm'       => 'Соц. сети',
    ];
    
    public function __construct()
    {
        $this->setTable('metrics_adwords');

        $this->addProperties([
            'source'    => [],
            'campaign'  => [],
            'src_type'  => [],
            'cross'     => [],
            'cost'      => [],
            'date'      => ['default' => date('Y-m-d')],
            'month'     => ['virtual' => true],
            'year'      => ['virtual' => true],
        ]);

        $this->addPropertyFilterIn('month', function ($model, $month) {
            $dt = \DateTime::createFromFormat('Y-m-d', $model->get('date'));
            $model->set('date', $dt->format('Y-' . str_pad($month, 2, 0, STR_PAD_LEFT) . '-01'));
            return true;
        });

        $this->addPropertyFilterIn('year', function ($model, $year) {
            $dt = \DateTime::createFromFormat('Y-m-d', $model->get('date'));
            $model->set('date', $dt->format($year . '-m-01'));
            return true;
        });

        $this->addPropertyFilterOut('month', function ($model) {
            $dt = \DateTime::createFromFormat('Y-m-d', $model->get('date'));
            return $dt->format('m');
        });

        $this->addPropertyFilterOut('year', function ($model) {
            $dt = \DateTime::createFromFormat('Y-m-d', $model->get('date'));
            return $dt->format('Y');
        });

        $this->addPlugin('visits', function($model) {
            $item = new Visit();
            $item->select()->where(['adwords_id' => $model->getId()]);

            return $item;
        }, ['independent' => true]);
    }
}