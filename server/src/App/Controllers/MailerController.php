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

    public function sendMail($subject, $body, $to, $isZeusUsername = FALSE) {
        $userController = $this->app['controller.user'];

        // Debug -> send all emails to the mail set in setting file
        if($this->app['settings']['DEBUG']) {
            $to = [$this->app['settings']['DEBUG_MAIL_RECIPIENT']];
        }
        // Other
        else {
            switch($to) {
                case MailerController::ALL_ADMIN:
                    $to = $userController->getAdminUsersEmails();
                    break;

                case MailerController::NEWS_SUBSCRIPTIONS:
                    $to = $userController->getNewsSubscriptionUsersEmails();
                    break;

                case MailerController::FTEBAY_SUBSCRIPTIONS:
                    $to = $userController->getFtebaySubscriptionUsersEmails();
                    break;

                default:
                    // Zeus username
                    if($isZeusUsername) {
                        $to = [$userController->getUserEmailFromZeusUsername()];
                    }
            }
        }

        if(is_array($to)) {
            $message = (new \Swift_Message($subject))
                ->setFrom(['mailer@fteturbojet.com' => 'Turbojet Mailer'])
                ->setTo($to)
                ->setBody($body, 'text/html')
            ;
        }

        return $this->app['mailer']->send($message);
    }
}
