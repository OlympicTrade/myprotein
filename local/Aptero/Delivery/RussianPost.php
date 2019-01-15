<?php
namespace Aptero\Delivery;

class RussianPost
{
    /**
     * @param $options
     * @return \DateTime
     */
    public function getDate($options = [])
    {
        $options = array_merge([
            'weight'      => '0',
            'summ'        => '0',
            'from_index'  => '198264',
            'to_index'    => '',
        ], $options);

        return (new \DateTime())->modify('+3 days');
    }

    /**
     * @param array $options
     * @return int
     */
    public function getPrice($options = [])
    {
        $options = $options + [
            'weight'      => '0',
            'summ'        => '0',
            'from_index'  => '198264',
            'to_index'    => '',
        ];

        $options['weight'] += 200;

        $str = '';
        foreach ($options as $key => $val) {
            $str .= '&' . $key . '=' . $val;
        }
        $str = ltrim($str, '&');

        $url = 'http://api.print-post.com/api/sendprice/v2/?' . $str;

        $resp = \Zend\Json\Json::decode(file_get_contents($url));

        $price = $resp->posilka_nds ? $resp->posilka_nds : $resp->posilka_price_nds;

        return ceil($price / 10) * 10;
    }
}