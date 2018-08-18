<?php

namespace App\Controllers;

use Silex\Application;
use App\Providers\UserProvider;
use Symfony\Component\HttpFoundation\Request;

class UserController {
    protected $app;
    protected $customerModel;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->userModel = $app['model.user'];
    }


    /**
    * Fetch all the users
    *
    * @return JsonResponse A 200 HTTP response containing an array with all the customers
    */
    public function getUsers(Request $request) {
        $queryParams = $request->query;
        $includeGraduated = (boolean)$queryParams->get('includeGraduated');

        if(($users = $this->userModel->getUsers($includeGraduated)) === false) {
            return $this->app->json(['message' => 'An error has occured during the users data retrieval'], 500);
        }

        return $this->app->json($users, 200);
    }

    /**
    * Fetch all the users
    *
    * @return JsonResponse A 200 HTTP response containing the user details
    */
    public function getUser($id) {
        $user = $this->userModel->getUserDetails($id);

        return $this->app->json($user, 200);
    }

    /**
    * Fetch all the users
    *
    * @return JsonResponse A 200 HTTP response containing the user details
    */
    public function getConnectedUser() {
        return $this->app->json($this->app['user'], 200);
    }

    public function createUser($username, $firstName, $lastname, $room, $group, $email, $phone) {

        if(($user = $this->userModel->createUser($username, $firstName, $lastname, $room, $group, $email, $phone)) === false) {
            return $this->app->json(['message' => 'An error has occured during the user creation'], 500);
        }

        return $this->app->json($user, 200);
    }

    /**
    * Edit the user
    *
    * @return JsonResponse A 200 HTTP response containing the user details
    */
    public function editUser(Request $request, $id) {
        $authorisedFields = [
            'email',
            'room_number',
            'phone',
            'callsign'
        ];

        $userData = $request->request->all();

        // Check the user to be modified is either the connected user or the user posseses the permission
        if(!$this->app['user']->hasPermission('permission_edit_user') && $this->app['user']->getId() !== $id) {
            return $this->app->json(['message' => 'You don\'t have the permission to edit this user'], 403);
        }

        // Check the user can edit the fields.
        // IF the user is superAdmin he can edit any field
        // Otherwise he is limited to the authorised list
        if(!$this->app['user']->getSuperAdmin() && count($userData) != count(array_intersect($authorisedFields, array_keys($userData))) > 0) {
            return $this->app->json(['message' => 'You don\'t have the permission to edit these fields'], 403);
        }

        if(isset($userData['groups']) && count($userData['groups']) === 0) {
            return $this->app->json(['message' => 'A user can\'t be affected to no group'], 403);
        }

        if(isset($userData['password'])) {
            $userProvider = new UserProvider($this->app['db']);

            // Try finding the user through his username and get the proper encoder
            $user = $userProvider->loadUserById($id);
            $encoder = $this->app['security.encoder_factory']->getEncoder($user);

            // Encode the password
            $userData['salt'] = $user->generateSalt();
            $userData['password'] = $encoder->encodePassword($userData['password'], $userData['salt']);
        }

        if(isset($userData['groups'])) {
            if(($user = $this->userModel->setGroups($id, $userData['groups'])) === false) {
                return $this->app->json(['message' => 'An error has occured during the user groups modification'], 500);
            }
            else {
                unset($userData['groups']);
            }
        }

        if(count($userData) > 0) {
            if(($user = $this->userModel->editUserDetails($id, $userData)) === false) {
                return $this->app->json(['message' => 'An error has occured during the user modification'], 500);
            }
        }

        return $this->app->json($user, 200);
    }

    public function getAllGroups(Request $request) {
        $queryParams = $request->query;
        $nonActive = (boolean)$queryParams->get('nonActive');

        $groups = $this->userModel->getAllGroups($nonActive);

        return $this->app->json($groups, 200);
    }

    public function getPicklistGroups(Request $request) {
        $groups = $this->userModel->getGroupsKeyValue();

        return $this->app->json($groups, 200);
    }

    public function getAdminUsersEmails() {
        return $this->userModel->getAdminUsers();
    }

    public function getUserEmailFromZeusUsername() {
        return $this->userModel->getUserFromZeusUsername();
    }

    public function getNewsSubscriptionUsersEmails() {
        return $this->userModel->getNewsSubscriptionUsers();
    }

    public function getFtebaySubscriptionUsersEmails() {
        return $this->userModel->getFtebaySubscriptionUsers();
    }
}
