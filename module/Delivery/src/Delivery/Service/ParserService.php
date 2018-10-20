<?php

namespace Delivery\Service;

use Aptero\Service\AbstractService;
use Delivery\Model\City;
use Delivery\Model\Delivery;
use Delivery\Model\Point;
use Delivery\Model\ShopLogistic;
use Zend\Db\Sql\Expression;

class ParserService extends AbstractService
{
    const TABLE_REGIONS = 'delivery_regions';
    const TABLE_CITIES  = 'delivery_cities';
    const TABLE_POINTS  = 'delivery_points';

}