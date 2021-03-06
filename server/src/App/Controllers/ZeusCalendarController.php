<?php

namespace App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

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
        $dateTo = date('d-m-Y', strtotime('+30 days'));

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

        // var_dump($newEvents);
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

        // var_dump($oldEvents);
        // exit();

        $diffEvents = $this->compareEvents($oldEvents, $newEvents);
        // $diffEvents = \array_values(\array_diff($newEvents, $oldEvents));

        // var_dump($diffEvents);
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
            if(!empty($oldEvents[$id]['crew1']) && !array_key_exists($oldEvents[$id]['crew1'], $usersMail)) {
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
            if(!empty($newEvents[$id]['crew1']) && !array_key_exists($newEvents[$id]['crew1'], $usersMail)) {
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
            if(!empty($newEvents[$id]['crew1']) && !array_key_exists($newEvents[$id]['crew1'], $usersMail)) {
                $usersMail[$newEvents[$id]['crew1']] = [];
                $usersMail[$newEvents[$id]['crew1']]['old'] = [];
                $usersMail[$newEvents[$id]['crew1']]['new'] = [];
            }

            $usersMail[$newEvents[$id]['captain']]['new'][] = $newEvents[$id];

            if(!empty($newEvents[$id]['crew1'])) {
                $usersMail[$newEvents[$id]['crew1']]['new'][] = $newEvents[$id];
            }
        }

        // var_dump($usersMail);
        // exit();
        
        $this->zeusCalendarModel->deleteEvents($diffEvents['deletions']);
        $this->zeusCalendarModel->insertEvents($diffEvents['insertions'], $newEvents);
        $this->zeusCalendarModel->updateEvents($diffEvents['updates'], $newEvents);


        $emailList = [];
        foreach ($usersMail as $user => $events) {
            $emailList[$user] = '';
            $emailList[$user] .= 'The following flights have been altered on the schedule. ';
            $emailList[$user] .= 'Please check Zeus.<br><br>';
            $emailList[$user] .= '<strong>Old Events:</strong><br>';
            foreach ($events['old'] as $event) {
                $emailList[$user] .= 'Title: ' . $event['exercise_title'] . '<br>Start Time: ' . $event['start'] . 'z<br>End Time: ' . $event['end'] . 'z<br>P1: ' . $event['captain']  . '<br>P2: ' . $event['crew1'] . '<br>Aircraft: ' .$event['registration']. '<br><br>';
            }
            $emailList[$user] .= '<strong>New Events:</strong><br>';

            foreach ($events['new'] as $event) {
                $emailList[$user] .= 'Title: ' . $event['exercise_title'] . '<br>Start Time: ' . $event['start'] . 'z<br>End Time: ' . $event['end'] . 'z<br>P1: ' . $event['captain']  . '<br>P2: ' . $event['crew1'] . '<br>Aircraft: ' .$event['registration']. '<br><br>';
            }
            $emailList[$user] .= '<br><br><span style="color: #D32F2F; font-size:small;">Disclaimer: Zeus is the only official source for your flights</span>';
        }
        var_dump($emailList);

        $mailResult = '';
        foreach ($emailList as $user => $body) {
            $mailResult = $this->app['controller.mailer']->sendMail('[Turbojet] A Flight Calendar Event Has Been Altered', $body, $user);
            // var_dump('mail sent ' . $mailResult );
        }

        /*
            Update/insert/delete
         */
        
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

        $zeusUserName = $this->app['user']->getCalendarZeusUsername();

        if(($events = $this->zeusCalendarModel->getUserEvents($zeusUserName)) === false) {
            return $this->app->json(['message' => 'An error has occured during the users events retrieval'], 500);
        }

        foreach ($events as &$event) {
            // Sending back UTC dates so they can be displayed in Madrid timezone in front
            $event['start'] = \DateTime::createFromFormat('Y-m-d H:i:s', $event['start'], new \DateTimeZone('UTC'))->format(\DateTime::ISO8601);
            $event['end'] = \DateTime::createFromFormat('Y-m-d H:i:s', $event['end'], new \DateTimeZone('UTC'))->format(\DateTime::ISO8601);
        }


        return $this->app->json($events, 200);
    }

    public function generateIcal(Request $request, $zeusUserName) {

        if(($events = $this->zeusCalendarModel->getUserEvents($zeusUserName)) === false) {
            return $this->app->json(['message' => 'An error has occured during the users events retrieval'], 500);
        }

        $icalEvents = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";

        foreach ($events as $event) {
            // 20180829T173000Z
            $start = \DateTime::createFromFormat('Y-m-d H:i:s', $event['start'], new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
            $end = \DateTime::createFromFormat('Y-m-d H:i:s', $event['end'], new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
            $icalEvents .= "BEGIN:VEVENT\r\nUID:" . $event['id'] . "@fteturbojet.com\r\nDTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z\r\nDTSTART:".$start."\r\nDTEND:".$end."\r\nSUMMARY: ".$event['exercise_title'] . ' - ' . $event['registration'] . "\r\nDESCRIPTION:Captain: " . $event['captain'] . "\\nP1: " . $event['crew1'] . "\\nRegistration: " . $event['registration'] .  "\r\nEND:VEVENT\r\n";
        }
        $icalEvents .= "END:VCALENDAR";

        return new Response(
            $icalEvents,
            200,
            array(
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'inline; filename="calendar.ics"'
            )
        );
    }
}
