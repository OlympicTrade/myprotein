<?php
namespace ApplicationAdmin\Model;

use Aptero\Db\Entity\Entity;

class Domain extends Entity
{
    public function __construct()
    {
        $this->setTable('domains');

        $this->addProperties([
            'depend'        => [],
            'domain'        => [],
            'mdomain'       => [],
            'city_name'     => [],
            'city_name_r'   => [],
            'city_name_i'   => [],
            'city_name_b'   => [],
            'robots'        => [],
            'html_head'     => [],
            'html_body'     => [],
            //'options'   => ['type' => Entity::PROPERTY_TYPE_JSON],
        ]);
    }
}