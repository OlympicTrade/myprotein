<?php
/**
Example

$module = new \Application\Model\Module();
$settings = $module->setModuleName('Events')->getPlugin('settings');

$sms = new SmsRu();
$sms->setOptions(array(
    'api_id'   => $settings->get('smsru-api_id'),
    'login'    => $settings->get('smsru-login'),
    'password' => $settings->get('smsru-password'),
));

$sms
    ->setAddress('79046366764')
    ->setMessage('Привет я робот!')
    ->send();
*/

namespace Aptero\Events\Notices\Sms;

use Aptero\Events\Notices\AbstractNotice;

class SmsRu extends AbstractNotice
{
    static protected $enabled = true;

    /**
     * @param $enabled bool
     */
    public function setEnabled($enabled)
    {
        self::$enabled = $enabled;
    }

    public function send()
    {

        $ch = curl_init("http://sms.ru/auth/get_token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $token = curl_exec($ch);
        curl_close($ch);

        $options = array(
            'login'		=>	$this->options['login'],
            'sha512'	=>	hash('sha512', $this->options['password'] . $token),
            'token'		=>	$token,
            'to'		=>	(is_array($this->address) ? implode(',', $this->address) : $this->address),
            'text'		=>	$this->message,
        );

        $ch = curl_init("http://sms.ru/sms/send");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = explode("\n", $result);

        return $result[0] == 100;
    }
}