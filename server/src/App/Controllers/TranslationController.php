<?php

namespace App\Controllers;

use Silex\Application;

class TranslationController {
    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function getTranslation($language) {
        $filePath = '../assets/translations/'.$language.'.json';

        if(!file_exists($filePath)) {
            return $this->app->json(['errorMessage' => 'language \''.$language.'\' introuvable'], 404);
        }

        $json = json_decode(file_get_contents($filePath));

        if($json !== NULL) {
            return $this->app->json($json, 200);
        }
        else {
            return $this->app->json(['errorMessage' => 'Impossible de décoder le fichier JSON'], 500);
        }
    }
}
