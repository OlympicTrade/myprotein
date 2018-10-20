<?php

namespace Catalog\Service;

use Aptero\Service\AbstractService;
use Catalog\Model\Order;

class PaymentService extends AbstractService
{
    public function payment($data)
    {
        $key = 'kpV1mYf2eIvzufty5pwqMWii';

        $hashData = [
            $data['notification_type'],
            $data['operation_id'],
            $data['amount'],
            $data['currency'],
            $data['datetime'],
            $data['sender'],
            $data['codepro'],
            $key,
            $data['label'],
        ];

        if(sha1(implode('&', $hashData)) != $data['sha1_hash']) {
            return false;
        }

        $order = new Order();
        $order->setId(str_replace('M', '', $data['label']));

        if(!$order->load()) {
            return false;
        }

        $order->set('paid', $data['amount'])->save();

        return true;
    }

    protected function getQiwiMdl()
    {

    }
}