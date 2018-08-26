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

        $email_start="<!DOCTYPE html><html lang='en' dir='ltr'><head><meta charset='utf-8'><title></title><style media='screen'>* {padding: 0px;margin: 0px;font-family: 'Open Sans', sans-serif;}body {background: #f6f6f6;}.card {background: #fff;border-radius: 2px;margin: 32px auto;position: relative;width: 95%;box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);}.title {padding: 16px;background: #1976d2;color: #fafafa;line-height: 64px;}.title h2 {display: inline;}.title img {width: 32px;height: 32px;position: relative;bottom: -8px;margin-left: 16px;}.content {padding: 32px;}.footer {background: #ddd;padding: 16px 32px;}</style></head><body><div class='card'><div class='title'><img src='http://api.fteturbojet.com/media/icon.png' />&nbsp;<h2>Turbojet mailer</h2></div><div class='content'>";
        $email_end="</div><div class='footer'><p>This is an automated message from Turbojet mailer. If you don't want to receive more updates, you can unsuscribe from these emails in your profile on <a href='https://fteturbojet.com'>www.fteturbojet.com</a></p><br><p>Made with ❤️ by your IT Reps.</p></div></div></body></html>";

        // Debug -> send all emails to the mail set in setting file
        if($this->app['settings']['DEBUG']) {
            $to = $this->app['settings']['DEBUG_MAIL_RECIPIENT'];
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
                        $to = $userController->getUserEmailFromZeusUsername($to);
                    }
            }
        }

        if(is_array($to)) {
            $message = (new \Swift_Message($subject))
                ->setFrom(['mailer@fteturbojet.com' => 'Turbojet Mailer'])
                ->setTo($to)
                ->setBody($email_start.$body.$email_end, 'text/html')
            ;
        }

        return $this->app['mailer']->send($message);
    }
}
