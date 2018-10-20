<?php
namespace Contacts\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ContactsFooter extends AbstractHelper
{
    /**
     * @var \Contacts\Model\Contacts
     */
    protected $contacts = null;

    public function __construct($contacts)
    {
        $this->contacts = $contacts;
    }

    public function __invoke()
    {
        $html =
            '<div class="contacts">'
                .'<div class="header"><a href="/contacts/">Контакты</a></div>'
                .'<div class="body">'
                    .$this->row('', 'email')
                    .$this->row('', 'skype')
                    .$this->row('', 'phone_1')
                    .$this->row('', 'address')
                .'</div>'
            .'</div>';

        return $html;
    }

    protected function row($name, $field)
    {
        $html = '';

        if($this->contacts->get($field)) {
            $html .=
                '<div class="row ' . $field . '">'
                    .'<div class="icon"><i class="fa"></i></div>'
                    . $this->contacts->get($field)
                .'</div>';
        }

        return $html;
    }
}