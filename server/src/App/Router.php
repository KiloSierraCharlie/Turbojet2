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
        $this->app->get('/news', 'controller.editorialContent:getNews');
        $this->app->get('/ftebay-posts', 'controller.editorialContent:getFTEPosts');
        $this->app->get('/documents/{collectionSlug}', 'controller.document:getDocuments');
        $this->app->post('/documents/{collectionSlug}', 'controller.document:addDocument');
        $this->app->post('/documents/{documentId}/edit', 'controller.document:editDocument'); // TODO use put
        $this->app->delete('/documents/{documentId}', 'controller.document:deleteDocument');
        $this->app->get('/users', 'controller.user:getUsers');
        $this->app->get('/users/{id}', 'controller.user:getUser');
        $this->app->post('/users/{id}', 'controller.user:editUser');
        $this->app->get('/translations/{language}', 'controller.translation:getTranslation')->bind('translation');
        $this->app->get('/groups', 'controller.user:getGroups');
        $this->app->get('/picklists/groups', 'controller.user:getPicklistGroups')->bind('picklist-groups');

        // $this->app['cors-enabled']($this->app);
    }
}
