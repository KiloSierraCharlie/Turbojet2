<?php

namespace App;

use Silex\Application;

class Router {
    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function initRoutes() {
        $this->app->post('/encoder', 'controller.auth:encode')->bind('encoder');
        $this->app->post('/login', 'controller.auth:login')->bind('login');
        $this->app->post('/register', 'controller.auth:register')->bind('register');
        $this->app->get('/menu', 'controller.menu:getDynamicMenu');
        $this->app->get('/editorial/{type}', 'controller.editorialContent:getPosts');
        $this->app->get('/editorial/{type}/{postId}', 'controller.editorialContent:getPost');
        $this->app->post('/editorial/{type}', 'controller.editorialContent:createPost');
        $this->app->post('/editorial/{type}/{postId}/edit', 'controller.editorialContent:editPost');
        $this->app->delete('/editorial/{type}/{postId}', 'controller.editorialContent:deletePost');
        $this->app->post('/editorial-image-upload', 'controller.editorialContent:uploadImage');
        $this->app->get('/documents/{collectionSlug}', 'controller.document:getDocuments');
        $this->app->post('/documents/{collectionSlug}', 'controller.document:addDocument');
        $this->app->post('/documents/{documentId}/edit', 'controller.document:editDocument'); // TODO use put
        $this->app->delete('/documents/{documentId}', 'controller.document:deleteDocument');
        $this->app->get('/user', 'controller.user:getConnectedUser');
        $this->app->get('/user/calendar', 'controller.zeusCalendarController:getUserCalendar');
        $this->app->get('/users', 'controller.user:getUsers');
        $this->app->get('/users/{id}', 'controller.user:getUser');
        $this->app->post('/users/{id}', 'controller.user:editUser');
        $this->app->get('/verify-users', 'controller.user:getUsersToVerify');
        $this->app->post('/verify-users/minivan/add/{id}', 'controller.user:addUserToMinivan');
        $this->app->post('/verify-users/minivan/remove/{id}', 'controller.user:removeUserFromMinivan');
        $this->app->post('/verify-users/verify/{id}', 'controller.user:verifyUser');
        $this->app->post('/verify-users/ban/{id}', 'controller.user:banUser');
        $this->app->post('/verify-users/unban/{id}', 'controller.user:unbanUser');
        $this->app->get('/groups', 'controller.user:getAllGroups');
        $this->app->get('/picklists/groups', 'controller.user:getPicklistGroups')->bind('picklist-groups');
        $this->app->get('/bookings/{type}', 'controller.booking:getBookings');
        $this->app->post('/bookings/{type}', 'controller.booking:createBooking');
        $this->app->post('/bookings/{type}/{id}/edit', 'controller.booking:editBooking');
        $this->app->post('/bookings/{type}/{id}/markAsPaid', 'controller.booking:markAsPaid');
        $this->app->post('/bookings/{type}/{id}/changeState', 'controller.booking:changeBookingState');
        $this->app->get('/bookings/{type}/calculatePrice', 'controller.booking:getBookingPrice');
        $this->app->get('/bookings/{type}/resources', 'controller.booking:getResources');
        $this->app->get('/zeus-calendar/parse', 'controller.zeusCalendarController:parseZeusCalendar')->bind('parseZeusCalendar');
        $this->app->get('/zeus-calendar/ical/{zeusUserName}', 'controller.zeusCalendarController:generateIcal')->bind('ical');

    }
}
