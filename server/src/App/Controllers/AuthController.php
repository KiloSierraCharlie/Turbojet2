<?php

namespace App\Controllers;

use Silex\Application;
use App\Providers\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class AuthController {
    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
    * Authenticate the user based on a user/password pair.
    * Will generate a new access token if the user is allowed to access the application.
    *
    * @param Request $request. Expects to receive in the request:
    * - string username The username.
    * - string password The plain password.
    *
    * @return JsonResponse An HTTP response containing either :
    * - A 200 code with the generated token
    * - A 401 code with the error message if the user can't be authentcated
    */
    public function login(Request $request) {
        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');

        $userProvider = new UserProvider($this->app['db']);

        try {
            // Try finding the user through his username and get the proper encoder
            $user = $userProvider->loadUserByUsername($username);
            $encoder = $this->app['security.encoder_factory']->getEncoder($user);

            // Check user is not banned and his account is verified
            if(!$user->getVerified() || $user->getBanned()) {
                return $this->app->json(['message' => 'Your account has been banned or is not yet validated'], 401);
            }

            // Check if the plain password is valid and it's value is equal to the encoded password
            if(!$encoder->isPasswordValid($user->getPassword() , $plainPassword, $user->getSalt())) {
                // Incorrect password
                $errorMsg = $this->app['debug'] === true ? 'Incorrect password' : 'Bad credentials, please try again';
                return $this->app->json(['message' => $errorMsg], 401);
            }

            // Credentials are valids, we can create the token and returns it to the user
            $userProvider->deleteUserTokens($username);
            $token = $userProvider->generateToken($username);

            return $this->app->json(['token' => $token]);

        } catch (UsernameNotFoundException $e) {
            // Incorrect username
            $errorMsg = $this->app['debug'] === true ? 'Incorrect username' : 'Bad credentials, please try again';
            return $this->app->json(['message' => $errorMsg], 401);
        }
    }


    public function register(Request $request) {

        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');
        $firstName = $request->request->get('firstName');
        $lastName = $request->request->get('lastName');
        $room = $request->request->get('room');
        $group = $request->request->get('group');
        $phone = $request->request->get('phone');
        $recaptcha = $request->request->get('captchaReponse');
        $file = $request->files->get('file');

        // Check for correctly filled fields
        if(!$username || !$plainPassword || !$firstName || !$lastName || !$room ||
           !$group || !$phone || !$recaptcha || !$file) {
            return $this->app->json(['message' => 'Please fill all fields correcly'], 400);
        }

        // check recaptcha
        $url = 'https://www.google.com/recaptcha/api/siteverify';
    	$data = array(
    		'secret' => $this->app['settings']['RECAPTCHA_SECRET'],
    		'response' => $recaptcha
    	);
    	$options = array(
    		'http' => array (
    			'method' => 'POST',
    			'content' => http_build_query($data),
                'header' => "Content-Type: application/x-www-form-urlencoded"

    		)
    	);
    	$context  = stream_context_create($options);
    	$verify = file_get_contents($url, false, $context);
    	$captchaSuccess = json_decode($verify);

        if ($captchaSuccess->success !== true) {
    		return $this->app->json(['message' => 'Invalid captcha verification. Please try again'], 400);
    	}

        // Create the user profile
        $userModel = $this->app['model.user'];
        $userProvider = new UserProvider($this->app['db']);

        //Check if email is already in the DB for a user
        try {
            $result = $userProvider->loadUserByUsername($username);

            //User already exists
            $response = ['message' => 'There is a user with this email already registered'];

            return $this->app->json($response, 400);

        } catch (UsernameNotFoundException $e) {
            //just continue execution
        }

        if(($result = $userModel->createUser($username, $firstName, $lastName, $room, $group, $phone, $file->getClientOriginalName())) instanceof \Exception) {
            $response = ['message' => 'An error has occured during the user creation'];

            if($this->app['debug'] === true) {
                $response['exception'] = $result->__toString();
            }

            return $this->app->json($response, 500);
        }

        $id = $result;

        // Move the file in the correct path
        if($file) {
            try {
                $picture_filename = $id . "." . pathinfo($file->getClientOriginalName(),PATHINFO_EXTENSION);
                $path = '../www/media/student_photos/';
                $file->move($path, $picture_filename);
            }
            catch(\Exception $e) {
                return $this->app->json(['message' => 'Error during the transfer of the file, please try again. If the problem persist please contact the IT Rep'], 403);
            }
        }

        try {
            // Try finding the user through his username and get the proper encoder
            $user = $userProvider->loadUserByUsername($username);
            $encoder = $this->app['security.encoder_factory']->getEncoder($user);

            // Encode the password
            $salt = $user->generateSalt();
            $encodedPassword = $encoder->encodePassword($plainPassword, $salt);

        } catch (UsernameNotFoundException $e) {
            // Incorrect username
            $errorMsg = $this->app['debug'] === true ? 'Incorrect username' : 'Bad credentials, please try again';
            return $this->app->json(['message' => $errorMsg], 401);
        }

        // Store the encoded password and salt
        if(($result = $userModel->setUserPassword($user->getId(), $salt, $encodedPassword)) instanceof \Exception) {
            $response = ['message' => 'An error has occured during the user password storage'];

            if($this->app['debug'] === true) {
                $response['exception'] = $result->__toString();
            }

            return $this->app->json($response, 500);
        }

        // Send an email to admins
        $body = 'A new user has registered and needs your validation:<br />
        <strong>First name:</strong> '.$user->getFirstName().'<br /><strong>Last name:</strong> '.$user->getLastName();

        $this->app['controller.mailer']->sendMail('[Turbojet] New user', $body, $this->app['controller.mailer']::ALL_ADMIN);

        return $this->app->json([], 200);
    }

    /**
    * Encode the given password based on user's encoder
    *
    * @param Request $request. Expects to receive:
    * - string username The username
    * - string password The plain password
    *
    * @return JsonResponse An HTTP response containing either a 200 code with the encoded password
    * or a 401 code with the error message if the username is not found
    */
    public function encode(Request $request) {
        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');

        $userProvider = new UserProvider($this->app['db']);

        try {
            // Try finding the user through his username and get the proper encoder
            $user = $userProvider->loadUserByUsername($username);
            $encoder = $this->app['security.encoder_factory']->getEncoder($user);

            // Encode the password
            $salt = $user->generateSalt();
            $encodedPassword = $encoder->encodePassword($plainPassword, $salt);

        } catch (UsernameNotFoundException $e) {
            // Incorrect username
            $errorMsg = $this->app['debug'] === true ? 'Incorrect username' : 'Bad credentials, please try again';
            return $this->app->json(['message' => $errorMsg], 401);
        }

        // User found, we can retuns an encoded password
        return $this->app->json(['encodedPassword' => $encodedPassword, 'salt' => $salt]);
    }
}
