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
        $this->app->get('/users', 'controller.user:getUsersFromGroup');
        $this->app->get('/users/{id}', 'controller.user:getUser');
        $this->app->post('/users/{id}', 'controller.user:editUser');
        $this->app->get('/translations/{language}', 'controller.translation:getTranslation')->bind('translation');
        $this->app->get('/groups', 'controller.group:getGroups');
        $this->app->get('/picklists/groups', 'controller.group:getPicklistGroups')->bind('picklist-groups');
    }
}
