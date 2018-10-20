<?php

namespace Contacts\Service;

use Aptero\Mail\Mail;
use Aptero\Service\AbstractService;
use Contacts\Model\Feedback;
use Contacts\Model\FeedbackFile;
use User\Service\AuthService;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class FeedbackService extends AbstractService implements ServiceManagerAwareInterface
{
    public function addMessage($data)
    {
        $feedback = new Feedback();
        $feedback->unserializeArray($data);

        $feedback->set('status', Feedback::STATUS_NEW);

        if($user = AuthService::getUser()) {
            $feedback->set('user_id', $user->getId());
        }

        $feedback->save();

        if(isset($_POST['attache'])) {
            $folder =  DATA_DIR . '/uploads/feedback/' . $feedback->getId();
            mkdir($folder, 0777, true);

            foreach($_POST['attache'] as $attache) {
                $file = new FeedbackFile();
                $file->setVariables(array(
                    'file'        => $attache,
                    'feedback_id' => $feedback->getId()
                ))->save();

                rename(
                    DATA_DIR . '/uploads/' . $attache,
                    $folder . '/' . $attache
                );
            }
        }

        return $feedback;
    }

    public function sendFeedbackMail(Feedback $feedback)
    {
        $feedbackModule = new \Application\Model\Module();
        $feedbackModule
            ->setModuleName('Contacts')
            ->setSectionName('Feedback')
            ->load();

        $mail = new Mail();

        $mail->setTemplate(MODULE_DIR . '/Contacts/view/contacts/mail/feedback.phtml')
            ->setHeader('Сообщение от пользователя')
            ->addTo($feedbackModule->getPlugin('settings')->get('email'))
            ->setVariables(['feedback' => $feedback]);

        foreach($feedback->getPlugin('files') as $file) {
            $mail->setAttachment($file->getFile());
        }

        $mail->send();
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}