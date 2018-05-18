<?php

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Security\Core\Encoder\Pbkdf2PasswordEncoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\EditorialContentController;
use App\Controllers\GroupController;
use App\Controllers\TranslationController;
use App\Models\UserModel;
use App\Models\EditorialContentModel;
use App\Models\GroupModel;
use App\Providers\UserProvider;
use App\Router;

$app = new Application();

// store settings
$app['settings'] = $settings;

// Accepting JSON payload
$app->before(function(Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

// Registering the CORS Provider for preflights requests (OPTIONS)
// https://github.com/jdesrosiers/silex-cors-provider
$app->register(new CorsServiceProvider(), $app['settings']['CORS'][$app['settings']['ENV']]);
$app->after($app["cors"]);

// Registering the ServiceController Provider
$app->register(new ServiceControllerServiceProvider());

// Registering Doctrine Provider
$app->register(new DoctrineServiceProvider(), array(
    'db.options' => $app['settings']['DATABASE'][$app['settings']['ENV']]
));

// Registering the Security Provider and setting the encoder to BCrypt
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array()
));
// $app['security.default_encoder'] = function ($app) {
//     return $app['security.encoder.pbkdf2'];
// };
//
$app['security.default_encoder'] = function ($app) {
    return new Pbkdf2PasswordEncoder('sha1', false, 1000, 20);
};

/*
* Controllers containers
*/
$app['controller.auth'] = function() use ($app) {
    return new AuthController($app);
};

$app['controller.translation'] = function() use ($app) {
    return new TranslationController($app);
};

$app['controller.user'] = function() use ($app) {
    return new UserController($app);
};

$app['controller.editorialContent'] = function() use ($app) {
    return new EditorialContentController($app);
};


/*
* Models containers
*/
$app['model.user'] = function() use ($app) {
    return new UserModel($app['db']);
};

$app['model.editorialContent'] = function() use ($app) {
    return new EditorialContentModel($app['db']);
};

/*
* Router initialization
*/
$app['router'] = function() use ($app) {
    return new Router($app);
};
$app['router']->initRoutes();

/*
* Before function to protect the application by token authentification
*/
$app->before(function(Request $request) use ($app) {
    $whitelist = ['login', 'encoder', 'translation', 'picklist-groups'];
    $method = $request->getMethod();
    $routeName = $request->get('_route');

    // Whitelisting OPTIONS method
    if($method === "OPTIONS") {
      return true;
    }

    // Check the apiKey
    $apiKey = $request->headers->get('ApiKey');

    if($apiKey !== $app['settings']['APIKEY']) {
      return $app->json(['message' => 'Access refused'], 401);
    }

    // Whitelisting some routes
    if (in_array($routeName, $whitelist) || $method === "OPTIONS" )  {
        return true;
    }

    // Test if the token has been passed to the API
    $token = $app['token'] = $request->headers->get('AuthToken');

    if ($token === null) {
        return $app->json(['message' => 'No token provided'], 401);
    }

    try {
        // Try finding the user through his token
        $app['user'] = (new UserProvider($app['db']))->loadUserByToken($token);
    } catch (TokenNotFoundException $e) {
        return $app->json(['message' => 'Token not found'], 401);
    } catch (AuthenticationExpiredException $e) {
        return $app->json(['message' => 'Token expired'], 401);
    }
});

return $app;
