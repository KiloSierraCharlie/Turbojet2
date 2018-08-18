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

        // Debug
        if($this->app['settings']['DEBUG']) {
            $to = [$this->app['settings']['DEBUG_MAIL_RECIPIENT']];
        }
        // Other
        else {
            // switch($to) {
            //     // TODO notifs to enable when in production
            //     case MailerController::ALL_ADMIN:
            //         $to = $userController->getAdminUsersEmails();
            //         break;
            //
            //     case MailerController::NEWS_SUBSCRIPTIONS:
            //         $to = $userController->getNewsSubscriptionUsersEmails();
            //         break;
            //
            //     case MailerController::FTEBAY_SUBSCRIPTIONS:
            //         $to = $userController->getFtebaySubscriptionUsersEmails();
            //         break;
            //
            //     // to test
            //     default:
            //         // Zeus username
            //         if(!strrpos($to, '@')) {
            //             $to = $userController->getUserEmailFromZeusUsername();
            //         }
            }
        }

        if(strrpos($to, '@') !== FALSE) {
            $message = (new \Swift_Message($subject))
                ->setFrom(['ftejerezstudentcommittee@gmail.com' => 'Turbojet Mailer'])
                ->setTo($to)
                ->setBody($body, 'text/html')
            ;
        }

        return $this->app['mailer']->send($message);
    }
}
