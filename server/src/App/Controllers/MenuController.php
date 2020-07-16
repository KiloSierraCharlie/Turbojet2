<?php

namespace App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MenuController {
    protected $app;
    protected $menuModel;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->menuModel = $app['model.menu'];
    }

    public function getDynamicMenu(Request $request) {
        $pagesTypes = array('page-sport', 'page-career', 'page-entertainment', 'page-covid', 'page-flightsafety');

        if(($result = $this->menuModel->getDynamicPages($pagesTypes)) instanceof \Exception) {
            return $this->app->json(['message' => 'An error has occured during the menu data retrieval', 'exception' => $result->__toString()], 500);
        }
        else {
            return $this->app->json(['pages' => $result], 200);
        }
    }
}
