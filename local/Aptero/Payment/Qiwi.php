<?php

namespace Aptero\Payment;

use Aptero\String\Translit;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;

class Qiwi
{
    public $shopId = 000000;
    public $restId = 00000000;
    public $restPass = '';
    public $currency = 'RUB';

    // mobile - оплата с мобильного телефона пользователя, qw - с любых источников оплаты Visa Qiwi Wallet
    public $paySource = 'qw';
    public $shopName = 'Shop name';
    public $debug = true;

    function __construct($options){
        $this->shopId   = $options['shopId'];
        $this->restId   = $options['restId'];
        $this->restPass = $options['restPass'];
        $this->shopName = $options['shopName'];
        $this->paySource = $options['paySource'];
        
        if(!function_exists('curl_init')){
            throw new Exception('CURL library not found on this server');
        }
    }

    private function __curl_start($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        return $ch;
    }

    /**
     * Выставление счета
     *
     * @param {string} tel Телефон пользователя, на которого выставляется счет
     * @param {int} amount Сумма счета
     * @param {string} date Срок годности счета (в формате ISO 8601)
     * @param {string} bill_id Уникальный номер счета
     * @param {string} comment Комментарий к платежу (не обязательно)
     * @returns {object} Объект ответа от сервера QIWI
     * @throws Exception
     */
    function create($tel, $amount, $date, $bill_id, $comment = null){
        $parameters = array(
            'user'       => 'tel:+' . $tel,
            'amount'     => $amount,
            'ccy'        => $this->currency,
            'comment'    => $comment,
            'pay_source' => $this->paySource,
            'lifetime'   => $date,
            'prv_name'   => $this->shopName,
        );

        $ch = $this->__curl_start('https://w.qiwi.com/api/v2/prv/' . $this->shopId . '/bills/' . $bill_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: text/json",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8"
        ));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->restId . ':' . $this->restPass);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $httpResponse = curl_exec($ch);
        if($this->debug)
            var_dump($httpResponse);
        if (!$httpResponse) {
            throw new Exception(curl_error($ch).'('.curl_errno($ch).')');
            return false;
        }
        $httpResponseAr = @json_decode($httpResponse);
        return $httpResponseAr->response;
    }
    /**
     * Возвращает ссылку на страницу оплаты счета. Используется в redir()
     *
     * @param {string} bill_id Уникальный номер счета
     * @param {string} success_url URL, на который пользователь будет переброшен в случае успешного проведения операции (не обязательно)
     * @param {string} fail_url URL, на который пользователь будет переброшен в случае неудачного завершения операции (не обязательно)
     * @return {string} Ссылка на страницу оплаты счета
     */
    function redir_link($bill_id, $success_url = '', $fail_url = ''){
        return "https://w.qiwi.com/order/external/main.action?shop=" . $this->shopId . "&transaction=" . $bill_id .
        "&successUrl=" . $success_url . "&failUrl=" . $fail_url;
    }

    /**
     * Переадресация на страницу оплаты счета
     *
     * @param {string} bill_id Уникальный номер счета
     * @param {string} success_url URL, на который пользователь будет переброшен в случае успешного проведения операции (не обязательно)
     * @param {string} fail_url URL, на который пользователь будет переброшен в случае неудачного завершения операции (не обязательно)
     */
    function redir($bill_id, $success_url = '', $fail_url = ''){
        header("Location: " . $this->redir_link($bill_id, $success_url, $fail_url));
    }

    /**
     * Информация о счете
     *
     * @param {string} bill_id Уникальный номер счета
     * @returns {object} Объект ответа от сервера QIWI
     * @throws Exception
     */
    function info($bill_id){
        $ch = $this->__curl_start('https://w.qiwi.com/api/v2/prv/'.$this->shopId.'/bills/'.$bill_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: text/json",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8"
        ));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->restId . ':' . $this->restPass);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $httpResponse = curl_exec($ch);
        if($this->debug)
            var_dump($httpResponse);
        if (!$httpResponse) {
            throw new Exception(curl_error($ch).'('.curl_errno($ch).')');
            return false;
        }
        $httpResponseAr = @json_decode($httpResponse);
        return $httpResponseAr->response;
    }


    /**
     * Отмена платежа
     *
     * @param {string} bill_id Уникальный номер счета
     * @returns {object} Объект ответа от сервера QIWI
     * @throws Exception
     */
    function reject($bill_id){
        $parameters = array(
            'status' => 'rejected'
        );
        $ch = $this->__curl_start('https://w.qiwi.com/api/v2/prv/'.$this->shopId.'/bills/'.$bill_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: text/json",
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8"
        ));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->restId . ':' . $this->restPass);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $httpResponse = curl_exec($ch);
        if($this->debug)
            var_dump($httpResponse);
        if (!$httpResponse) {
            throw new Exception(curl_error($ch).'('.curl_errno($ch).')');
            return false;
        }
        $httpResponseAr = @json_decode($httpResponse);
        return $httpResponseAr->response;
    }
}