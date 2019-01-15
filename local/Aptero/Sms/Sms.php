<?php

namespace Aptero\Sms;

class Sms
{
    protected $options = [
        'login'     => '',
        'password'  => '',
        'key'       => '',
        'sender'    => '',
        'test'      => '0',
        'flash'     => '0',
        'vider'     => '0',
    ];

    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function send($phones, $message, $options = [])
    {
        $options = array_merge($this->options, $options);

        //$message = Translit::ruToEn(str_replace("\n", ' ', $message));
        $message = str_replace("\n", ' ', $message);

        $params = [
            'charset'   => 'utf-8',
            'login'     => $options['login'],
            'psw'       => md5($options['password']),
            'phones'    => $phones,
            'mes'       => $message,
            'sender'    => $options['sender'],
            'cost'      => (int) $options['test'],
            'flash'     => (int) $options['flash'],
            'translit'  => (int) 0,
            'viber'     => (int) $options['viber'],
            'maxsms'    => 2,
        ];

        $url = 'http://smsc.ru/sys/send.php?';

        foreach ($params as $key => $val) {
            $url .= $key . '=' . $val . '&';
        }

        if(MODE == 'public') {
            @file_get_contents($url);
        } else {
            \Aptero\Debug\Chrome::log($message);
        }

        return;
    }
}