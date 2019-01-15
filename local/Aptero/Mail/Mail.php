<?php

namespace Aptero\Mail;

use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;

class Mail
{
    protected $mail;

    /**
     * @var array
     */
    protected $variables = array();

    /**
     * @var string
     */
    protected $header = '';

    /**
     * @var \Zend\View\Renderer\PhpRenderer
     */
    protected $view;

    /**
     * @var \Zend\View\Model\ViewModel
     */
    protected $viewModel;

    /**
     * @var \Zend\Mail\Transport\Smtp
     */
    protected $transport;

    /**
     * @var array
     */
    protected $attachments = array();

    /**
     * @var Message
     */
    protected $message = array();
    
    static protected $options = [];
    
    public function __construct ()
    {
        $this->view = new PhpRenderer();
        $this->message = new Message();
        $this->message->setEncoding('UTF-8');
        $this->message->addFrom(self::$options['sender']['email'], self::$options['sender']['name']);

        $this->transport = new Smtp();
        $this->transport->setOptions(new SmtpOptions(self::$options['connection']));
    }
    
    static public function setOptions($options)
    {
        self::$options = $options;
    }

    public function addTo($email)
    {
        $this->message->addTo($email);

        return $this;
    }

    public function setTemplate($path)
    {
        $resolver = new TemplateMapResolver();

        $resolver->setMap(array(
            'mailLayout'    => MODULE_DIR . '/Application/view/mail/layout.phtml',
            'mailTemplate'  => $path
        ));

        $this->view->setResolver($resolver);

        return $this;
    }

    public function setHeader($header)
    {
        $this->header = $header;
        $this->message->setSubject($header);

        return $this;
    }

    public function setVariable($key, $value)
    {
        $this->variables[$key] = $value;

        return $this;
    }

    public function setVariables($variables)
    {
        $this->variables = array_merge($this->variables, $variables);

        return $this;
    }

    public function setAttachment($file)
    {
        $attachment = new MimePart(fopen($file, 'r'));
        $attachment->type = mime_content_type($file);
        $attachment->encoding    = Mime::ENCODING_BASE64;
        $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;

        $this->attachments[] = $attachment;

        return $this;
    }

    public function send()
    {
        //Render message
        $viewModel = new ViewModel();
        $viewModel->setTemplate('mailTemplate');
        $viewModel->setVariables($this->variables);

        $content = $this->view->render($viewModel);

        //Render template
        $viewLayout = new ViewModel();
        $viewLayout->setTemplate('mailLayout')
            ->setVariables(array(
                'content' => $content,
                'header'  => $this->header,
            ));

        //Send mail
		$html = $this->view->render($viewLayout);
        
        //die($html);

        $html = new MimePart($html);
        $html->type = "text/html";

        array_unshift($this->attachments, $html);

        $body = new MimeMessage();
        $body->setParts($this->attachments);

        $this->message->setBody($body);
        
        if(MODE != 'dev') {
            $this->transport->send($this->message);
        }
    }
}