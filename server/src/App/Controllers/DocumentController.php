<?php

namespace App\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class DocumentController {
    protected $app;
    protected $documentModel;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->documentModel = $app['model.document'];
    }

    /**
    * Fetch all the users
    */
    public function getDocuments(Request $request, $collectionSlug) {
        $queryParams = $request->query;

        if(($documents = $this->documentModel->getDocumentsFromCollection($collectionSlug)) === false) {
            return $this->app->json(['message' => 'An error has occured during the documents data retrieval'], 500);
        }

        return $this->app->json($documents, 200);
    }

    public function addDocument(Request $request, $collectionSlug) {
        $name = $request->request->get('name');
        $link = $request->request->get('link');
        $file = $request->files->get('file');
        $fileName = $file ? $file->getClientOriginalName() : '';

        // TODO check type / size

        // Check the user has the permission to edit documents
        if(!$this->app['user']->hasPermission('permission_edit_document')) {
            return $this->app->json(['message' => 'You don\'t have the permission to edit documents'], 403);
        }

        // Check for correctly filled fields
        if(!$name || (!$file && !$link)) {
            return $this->app->json(['message' => 'Please fill all fields correcly'], 403);
        }

        // Get the collection id
        if(($collectionId = $this->documentModel->getCollectionIdFromSlug($collectionSlug)) === false) {
            return $this->app->json(['message' => 'Incorrect document collection'], 500);
        }

        // Add the document in base
        if($this->documentModel->add($name, $collectionId, $fileName, $link) === false) {
            return $this->app->json(['message' => 'Error during the storage in base of the document data'], 500);
        }

        // Move the file in the correct path. We do it at the last moment to avoid orphan files
        if($file) {
            try {
                $path = '../www/media/documents/';
                $file->move($path, $file->getClientOriginalName());
            }
            catch(\Exception $e) {
                return $this->app->json(['message' => 'Error during the transfer of the file, please try again. If the problem persist please contact the IT Rep'], 403);
            }
        }

        return $this->app->json(null, 200);
    }
}
