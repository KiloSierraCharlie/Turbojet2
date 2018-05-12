<?php

namespace App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class GroupController {
    protected $app;
    protected $groupModel;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->groupModel = $app['model.group'];
    }

    public function getGroups(Request $request) {
        $queryParams = $request->query;
        $nonActive = (boolean)$queryParams->get('nonActive');

        $groups = $this->groupModel->getGroups($nonActive);

        return $this->app->json($groups, 200);
    }

    public function getPicklistGroups(Request $request) {
        $groups = $this->groupModel->getGroupsKeyValue();

        return $this->app->json($groups, 200);
    }
}
