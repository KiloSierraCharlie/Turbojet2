<?php

namespace App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;

class ZeusCalendarController {
    // protected $bookingModel;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->zeusCalendarModel = $app['model.zeusCalendar'];
    }

    public function parseZeusCalendar(Request $request) {
        /*
            Get all events at +30days
         */
        $dateFrom = date('d-m-Y');
        $dateTo = date('d-m-Y', strtotime('+1 days'));

        $url = 'http://chronos.ftejerez.com:8090/chronos/services/crm/apps/login_handler.php';

        $data = array(
            'startDate' => $dateFrom,
            'finishDate' => $dateTo,
            'checkAll' => '1',
            'showUser' => '1',
            'islogged' => '1'
        );
        $options = array(
            'http' => array (
                'method' => 'POST',
                'content' => http_build_query($data),
                'header' => "Content-Type: application/x-www-form-urlencoded"

            )
        );
        $context  = stream_context_create($options);
        $xml = file_get_contents($url, false, $context);

        $newEvents = array();

        $crawler = new Crawler($xml);
        $crawler->filter('userLogin userEvents userEvent')->each(function (Crawler $node, $i) use (&$newEvents) {
            // idEvent 0 is a DI duty, we skip it
            if($node->filter('idEvent')->text() !== '0') {
                $start = \DateTime::createFromFormat('d/m/Y H:i', $node->filter('eventStartDate')->text() . ' ' . $node->filter('eventStartTime')->text(), new \DateTimeZone('UTC'));
                $end = \DateTime::createFromFormat('d/m/Y H:i', $node->filter('eventStartDate')->text() . ' ' . $node->filter('eventFinishTime')->text(), new \DateTimeZone('UTC'));

                $newEvents['event_'.$node->filter('idEvent')->text()] = array(
                    'id' => $node->filter('idEvent')->text(),
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                    'captain' => $node->filter('captain')->text(),
                    'crew1' => $node->filter('crew1')->text(),
                    'exercise_title' => $node->filter('exerciseTitle')->text(),
                    'registration' => $node->filter('registration')->text()
                );
            }
        });

        var_dump($newEvents);
        // exit();

        /*
            Delete all events in base already passed
        */
        $this->zeusCalendarModel->deleteOldEvents();

        /*
            Get difference old / new in an array
        */
        $oldEvents = [];
        foreach ($this->zeusCalendarModel->getAllEvents() as $event) {
            // $oldEvents[] = json_encode($event);
            $oldEvents['event_'.$event['id']] = $event;
        }

        var_dump($oldEvents);
        // exit();

        $diffEvents = $this->compareEvents($oldEvents, $newEvents);
        // $diffEvents = \array_values(\array_diff($newEvents, $oldEvents));

        var_dump($diffEvents);
        // exit();

        /*
            Notify user about differences
         */
        $usersMail = [];
        foreach ($diffEvents['deletions'] as $id) {
            if(!array_key_exists($oldEvents[$id]['captain'], $usersMail)) {
                $usersMail[$oldEvents[$id]['captain']] = [];
                $usersMail[$oldEvents[$id]['captain']]['old'] = [];
                $usersMail[$oldEvents[$id]['captain']]['new'] = [];
            }
            if(!empty($oldEvents[$id]['crew1']) && array_key_exists($oldEvents[$id]['crew1'], $usersMail)) {
                $usersMail[$oldEvents[$id]['crew1']] = [];
                $usersMail[$oldEvents[$id]['crew1']]['old'] = [];
                $usersMail[$oldEvents[$id]['crew1']]['new'] = [];
            }

            $usersMail[$oldEvents[$id]['captain']]['old'][] = $oldEvents[$id];

            if(!empty($oldEvents[$id]['crew1'])) {
                $usersMail[$oldEvents[$id]['crew1']]['old'][] = $oldEvents[$id];
            }
        }

        foreach ($diffEvents['updates'] as $id) {
            if(!array_key_exists($newEvents[$id]['captain'], $usersMail)) {
                $usersMail[$newEvents[$id]['captain']] = [];
                $usersMail[$newEvents[$id]['captain']]['old'] = [];
                $usersMail[$newEvents[$id]['captain']]['new'] = [];
            }
            if(!empty($newEvents[$id]['crew1']) && array_key_exists($newEvents[$id]['crew1'], $usersMail)) {
                $usersMail[$newEvents[$id]['crew1']] = [];
                $usersMail[$newEvents[$id]['crew1']]['old'] = [];
                $usersMail[$newEvents[$id]['crew1']]['new'] = [];
            }

            $usersMail[$newEvents[$id]['captain']]['old'][] = $oldEvents[$id];
            $usersMail[$newEvents[$id]['captain']]['new'][] = $newEvents[$id];

            if(!empty($newEvents[$id]['crew1'])) {
                $usersMail[$newEvents[$id]['crew1']]['old'][] = $oldEvents[$id];
                $usersMail[$newEvents[$id]['crew1']]['new'][] = $newEvents[$id];
            }
        }

        foreach ($diffEvents['insertions'] as $id) {
            if(!array_key_exists($newEvents[$id]['captain'], $usersMail)) {
                $usersMail[$newEvents[$id]['captain']] = [];
                $usersMail[$newEvents[$id]['captain']]['old'] = [];
                $usersMail[$newEvents[$id]['captain']]['new'] = [];
            }
            if(!empty($newEvents[$id]['crew1']) && array_key_exists($newEvents[$id]['crew1'], $usersMail)) {
                $usersMail[$newEvents[$id]['crew1']] = [];
                $usersMail[$newEvents[$id]['crew1']]['old'] = [];
                $usersMail[$newEvents[$id]['crew1']]['new'] = [];
            }

            $usersMail[$newEvents[$id]['captain']]['new'][] = $newEvents[$id];

            if(!empty($newEvents[$id]['crew1'])) {
                $usersMail[$newEvents[$id]['crew1']]['new'][] = $newEvents[$id];
            }
        }

        var_dump($usersMail);
        // exit();

        $emailList = [];
        foreach ($usersMail as $user => $events) {
            $emailList[$user] = '';
            $emailList[$user] .= 'The following flights have been altered on the schedule. ';
            $emailList[$user] .= 'Please check Zeus.<br><br>';
            $emailList[$user] .= 'DISCLAIMER: ZEUS IS THE OFFICIAL SOURCE OF DATA FOR YOUR FLIGHTS. YOU SHOULD NOT USE TURBOJET NOR ITS EMAIL NOTIFICATIONS AS THE SOLE SOURCE OF INFORMATION FOR Y0UR EVENTS.<br><br>';
            $emailList[$user] .= '<strong>Old Events:</strong><br>';
            foreach ($events['old'] as $event) {
                $emailList[$user] .= 'Title: ' . $event['exercise_title'] . '<br>Start Time: ' . $event['start'] . 'z<br>End Time: ' . $event['end'] . 'z<br>P1: ' . $event['captain']  . '<br>P2: ' . $event['crew1'] . '<br>Aircraft: ' .$event['registration']. '<br><br>';
            }
            $emailList[$user] .= '<strong>New Events:</strong><br>';
            foreach ($events['new'] as $event) {
                $emailList[$user] .= 'Title: ' . $event['exercise_title'] . '<br>Start Time: ' . $event['start'] . 'z<br>End Time: ' . $event['end'] . 'z<br>P1: ' . $event['captain']  . '<br>P2: ' . $event['crew1'] . '<br>Aircraft: ' .$event['registration']. '<br><br>';
            }
        }
        var_dump($emailList);

        $mailResult = '';
        foreach ($emailList as $user => $body) {

            // TODO get user email
            $mailResult = $this->app['controller.mailer']->sendMail('[Turbojet] A Flight Calendar Event Has Been Altered', $body, $user);
            var_dump('mail sent ' . $mailResult );
            // exit();
        }

        /*
            Update/insert/delete
         */
        $this->zeusCalendarModel->deleteEvents($diffEvents['deletions']);
        $this->zeusCalendarModel->insertEvents($diffEvents['insertions'], $newEvents);
        $this->zeusCalendarModel->updateEvents($diffEvents['updates'], $newEvents);

        var_dump($diffEvents);
        // exit();

        return $this->app->json([], 200);
    }

    private function compareEvents($old, $new) {
        $idsToDelete = [];
        $idsToUpdate = [];
        $idsNotChanged = [];
        $idsToInsert = [];
        foreach ($old as $id => $event) {
            // Event deleted
            if(!array_key_exists($id, $new)) {
                // var_dump(json_encode($event));
                // exit();
                $idsToDelete[] = $id;
            }

            else {
                if(json_encode($event) !== json_encode($new[$id])) {
                    // var_dump(json_encode($event));
                    // exit();
                    // var_dump($id);
                    // exit();
                    // var_dump(json_encode($new[$id]));
                    // exit();
                    $idsToUpdate[] = $id;
                }
                else {
                    $idsNotChanged[] = $id;
                }
            }
        }

        foreach ($new as $id => $event) {
            // New events
            if(!array_key_exists($id, $old)) {
                $idsToInsert[] = $id;
            }
        }

        return ['deletions' => $idsToDelete, 'updates' => $idsToUpdate, 'insertions' => $idsToInsert, 'nochange' => $idsNotChanged];
    }

    public function getUserCalendar(Request $request) {
        $queryParams = $request->query;
        $dateFrom = $queryParams->get('dateFrom');
        $dateTo = $queryParams->get('dateTo');

        return $this->app->json($events, 200);
    }

}
