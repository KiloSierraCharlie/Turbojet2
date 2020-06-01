<?php

namespace App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class BookingController {
    protected $app;
    protected $bookingModel;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->bookingModel = $app['model.booking'];
    }

    public function getBookings(Request $request, $type) {
        $queryParams = $request->query;
        $dateFrom = $queryParams->get('dateFrom');
        $dateTo = $queryParams->get('dateTo');

        if(!$this->isAllowedType($type)) {
            return $this->app->json(['message' => 'this type of booking is not allowed'], 400);
        }

        if(($result = $this->bookingModel->getBookings($type, $dateFrom, $dateTo)) instanceof \Exception) {
            return $this->app->json(['message' => 'An error has occured during the booking data retrieval', 'exception' => $result->__toString()], 500);
        }
        else {
            return $this->app->json($result, 200);
        }
    }

    public function getBookingPrice(Request $request, $type) {
        $queryParams = $request->query;
        $start = $queryParams->get('start');
        $end = $queryParams->get('end');

        if(!$this->isAllowedType($type)) {
            return $this->app->json(['message' => 'this type of booking is not allowed'], 400);
        }

        $price = $this->calculatePrice($type, $start, $end);

        return $this->app->json([
            'price' => $price,
            'type' => $type,
            'start' => $start,
            'end' => $end
        ], 200);
    }

    public function getResources(Request $request, $type) {
        $queryParams = $request->query;

        if(!$this->isAllowedType($type)) {
            return $this->app->json(['message' => 'this type of booking is not allowed'], 400);
        }

        if(($result = $this->bookingModel->getResources($type)) instanceof \Exception) {
            return $this->app->json(['message' => 'An error has occured during the resources data retrieval', 'exception' => $result->__toString()], 500);
        }
        else {
            return $this->app->json($result, 200);
        }
    }

    public function createBooking(Request $request, $type) {
        $start = $request->request->get('start');
        $end = $request->request->get('end');
        $resourceId = $request->request->get('resourceId');
        $userId = $this->app['user']->getId();
        $bookingReason = $request->request->get('bookingReason');

        if(!$this->isAllowedType($type)) {
            return $this->app->json(['message' => 'this type of booking is not allowed'], 400);
        }

        // Check for correctly filled fields
        if(!$bookingReason || !$start || !$end) {
            return $this->app->json(['message' => 'Please fill all fields correcly'], 400);
        }

        // Check the user has the permission to edit offers or is the author
        if($type === 'minivan' && !$this->app['user']->hasPermission('permission_make_minivan_booking')) {
            return $this->app->json(['message' => 'You don\'t have the permission to create this booking'], 403);
        }

        // Calculate price
        $price = $this->calculatePrice($type, $start, $end);

        // Create the booking in base
        if($this->bookingModel->add($type, $start, $end, $bookingReason, $userId, $price, $resourceId) === false) {
            return $this->app->json(['message' => 'Error during the storage in base of the booking'], 500);
        }
        else {
            return $this->app->json(null, 200);
        }
    }

    public function editBooking(Request $request, $type, $id) {
        $bookingReason = $request->request->get('bookingReason');

        // Check for correctly filled fields
        if(!$bookingReason) {
            return $this->app->json(['message' => 'Please fill all fields correcly'], 400);
        }

        $authorId = $this->bookingModel->getBookingAuthorId($id);

        // Check the user has the permission to edit booking or is the author
        if(!$this->app['user']->hasPermission('permission_edit_booking') && $this->app['user']->getId() !== $authorId) {
            return $this->app->json(['message' => 'You don\'t have the permission to edit this booking'], 403);
        }

        // Edit the booking in base
        if($this->bookingModel->edit($id, $bookingReason) === false) {
            return $this->app->json(['message' => 'Error during the storage in base of the booking'], 500);
        }
        else {
            return $this->app->json(null, 200);
        }
    }

    public function markAsPaid(Request $request, $type, $id) {
        // Check the user has the permission to edit booking
        if(!$this->app['user']->hasPermission('permission_edit_booking')) {
            return $this->app->json(['message' => 'You don\'t have the permission to edit this booking'], 403);
        }

        // Edit the booking in base
        if($this->bookingModel->markAsPaid($id) === false) {
            return $this->app->json(['message' => 'Error during the modification of the booking'], 500);
        }
        else {
            return $this->app->json(null, 200);
        }
    }

    public function changeBookingState(Request $request, $type, $id) {
        $authorId = $this->bookingModel->getBookingAuthorId($id);

        $isCancelled = $request->request->get('cancelled');

        // Check the user has the permission to edit offers or is the author
        if(!$this->app['user']->hasPermission('permission_edit_booking') && $this->app['user']->getId() !== $authorId) {
            return $this->app->json(['message' => 'You don\'t have the permission to edit this booking'], 403);
        }

        // Check if cancelling past event
        if (strtotime($this->bookingModel->getBookingStartDate($id)) < (time()+(60*60*1))) {
            return $this->app->json(['message' => 'You can\'t cancel a booking in the past'], 403);
        }

        // Change cancelled flag in base
        if(($result = $this->bookingModel->changeState($id, $isCancelled)) instanceof \Exception) {
            return $this->app->json(['message' => 'An error has occured during the cancellation of the booking', 'exception' => $result->__toString()], 500);
        }
        else {
            return $this->app->json(null, 200);
        }
    }

    private function isAllowedType($type) {
        $allowedTypes = ['minivan', 'tv', 'barbecue', 'gym', 'tennis'];

        return in_array($type, $allowedTypes);
    }

    private function calculatePrice($type, $start, $end) {
        $minutesBooked = round(abs(strtotime($start) - strtotime($end)) / 60);

        switch($type) {
            case 'minivan':
                $firstHourRate = 10.0;
                $hourRate = 5.0;
                if ($minutesBooked <= 60) {
                    $price = ($firstHourRate / 60) * $minutesBooked;
                } else {
                    $minuteRate = $hourRate / 60;
                    $price = $firstHourRate + ($minutesBooked - 60) * $minuteRate;
                }
                return round($price, 2);
            default:
                return -1;
        };

    }
}
