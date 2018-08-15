<?php

namespace App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MailerController {
    protected $app;

    const ALL_ADMIN = 'admin';
    const NEWS_SUBSCRIPTIONS = 'news_subscription';
    const FTEBAY_SUBSCRIPTIONS = 'ftebay_subscription';

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function sendMail($subject, $body, $to) {
        $userController = $this->app['controller.user'];

        if($this->app['settings']['DEBUG']) {
            $to = [$this->app['settings']['DEBUG_MAIL_RECIPIENT']];
        }
        else {
            switch($to) {
                // case MailerController::ALL_ADMIN:
                //     $to = $userController->getAdminUsers();
                //     break;
                //
                // case MailerController::NEWS_SUBSCRIPTIONS:
                //     $to = $userController->getNewsSubscriptionUsers();
                //     break;
                //
                // case MailerController::FTEBAY_SUBSCRIPTIONS:
                //     $to = $userController->getFtebaySubscriptionUsers();
                //     break;
            }
        }

        $message = (new \Swift_Message($subject))
            ->setFrom(['ftejerezstudentcommittee@gmail.com' => 'Turbojet Mailer'])
            ->setTo($to)
            ->setBody($body, 'text/html')
        ;

        return $this->app['mailer']->send($message);
    }
}
