<?php

namespace App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class EditorialContentController {
    protected $app;
    protected $newModel;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->editorialContentModel = $app['model.editorialContent'];
    }

    /**
    * Fetch all the news
    *
    * @return JsonResponse A 200 HTTP response containing an array with all the news
    */
    public function getNews(Request $request) {
        $queryParams = $request->query;
        $from = (int)$queryParams->get('from');
        $length = (int)$queryParams->get('length');

        if(($news = $this->editorialContentModel->getnews($from, $length)) === false) {
            return $this->app->json(['message' => 'An error has occured during the news data retrieval'], 500);
        }

        return $this->app->json($news, 200);
    }

    public function getFTEbayOffers() {
        if(($posts = $this->editorialContentModel->getFTEbayOffers()) === false) {
            return $this->app->json(['message' => 'An error has occured during the ftebay data retrieval'], 500);
        }

        return $this->app->json($posts, 200);
    }
}
