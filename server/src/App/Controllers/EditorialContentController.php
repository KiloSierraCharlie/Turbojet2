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

    public function createFTEbayOffer(Request $request) {
        $content = $request->request->get('content');
        $title = $request->request->get('title');

        // Check for correctly filled fields
        if(!$content || !$title) {
            return $this->app->json(['message' => 'Please fill all fields correcly'], 400);
        }

        // Add the offer in base
        if($this->editorialContentModel->addFTEbayOffer($this->app['user']->getId(), $content, $title) === false) {
            return $this->app->json(['message' => 'Error during the storage in base of the document data'], 500);
        }

        return $this->app->json(null, 200);
    }

    public function editFTEbayOffer(Request $request, $offerId) {
        $content = $request->request->get('content');
        $title = $request->request->get('title');

        $authorId = $this->editorialContentModel->getFTEbayOfferAuthorId($offerId);

        // Check the user has the permission to edit offers or is the author
        if(!$this->app['user']->hasPermission('permission_edit_ftebay_listing') && $this->app['user']->getId() !== $authorId) {
            return $this->app->json(['message' => 'You don\'t have the permission to edit this user'], 403);
        }

        // Check for correctly filled fields
        if(!$content || !$title) {
            return $this->app->json(['message' => 'Please fill all fields correcly'], 400);
        }

        // Add the offer in base
        if($this->editorialContentModel->editFTEbayOffer($offerId, $content, $title) === false) {
            return $this->app->json(['message' => 'Error during the storage in base of the document data'], 500);
        }

        return $this->app->json(null, 200);
    }

    public function deleteFTEbayOffer(Request $request, $offerId) {
        // Check the user has the permission to edit offers
        if(!$this->app['user']->hasPermission('permission_edit_ftebay_listing')) {
            return $this->app->json(['message' => 'You don\'t have the permission to edit FTEbay offers'], 400);
        }

        // Add the document in base
        if(($this->editorialContentModel->deleteFTEbayOffer($offerId)) === false) {
            return $this->app->json(['message' => 'Error during the FTEbay offer deletion from database'], 500);
        }

        return $this->app->json(null, 200);
    }

    public function uploadImage(Request $request) {
        $file = $request->files->get('file');

        // Check for correctly filled fields
        if(!$file ) {
            return $this->app->json(['message' => 'Please fill all fields correcly'], 400);
        }


        // Move the file in the correct path. We do it at the last moment to avoid orphan files
        try {

            $fileNameArray = explode('.', $file->getClientOriginalName());
            $fileExtension = array_pop($fileNameArray);
            $fileName = join('.', $fileNameArray) . '_' . uniqid() . '.' . $fileExtension;

            $file->move('../www/media/editorial/', $fileName);
        }
        catch(\Exception $e) {
            return $this->app->json(['message' => 'Error during the transfer of the file, please try again. If the problem persist please contact the IT Rep'], 403);
        }

        return $this->app->json(['file' => ['filename' => $fileName, 'path' => $this->siteURL() . '/media/editorial/' . $fileName]], 200);
    }

    private function siteURL() {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||  $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol.$domainName;
    }
}
