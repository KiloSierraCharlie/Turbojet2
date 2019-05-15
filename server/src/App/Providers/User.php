<?php
/**
* Class to comply with Silex implementation of the connected user
*/
namespace App\Providers;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class User implements UserInterface, EquatableInterface {
    private $username;
    private $password;
    private $salt;
    private $roles;

    public function __construct($username, $password, $salt, array $roles, $user, $groups)
    {
        // Note: roles is a synfony interface field not used

        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
        $this->id = $user['id'];
        $this->firstName = $user['first_name'];
        $this->lastName = $user['last_name'];
        $this->room = $user['room'];
        $this->banned = (int) $user['banned'];
        $this->verified = (int) $user['verified'];
        $this->superAdmin = (int) $user['super_admin'];
        $this->callsign = $user['callsign'];
        $this->position = $user['position'];
        $this->phone = $user['phone'];
        $this->picture = $user['picture'];
        $this->calendarGroundschool = $user['calendar_groundschool'];
        $this->calendarZeusUsername = $user['calendar_zeus_username'];
        $this->notification_zeus = $user['notification_zeus'];
        $this->notification_news = $user['notification_news'];
        $this->notification_ftebay = $user['notification_ftebay'];
        $this->groups = $groups;

        $this->userPermissions = array();
        foreach ($user as $key => $value) {
            if(strpos($key, 'permission_') === 0) {
                $this->userPermissions[$key] = $value;
            }
        }
    }

    public function getRoles() {
        return $this->roles;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function getUsername() {
        return $this->username;
    }

    public function eraseCredentials() {
    }

    public function getId() {
        return $this->id;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getRoom() {
        return $this->room;
    }

    public function getBanned() {
        return $this->banned;
    }

    public function getVerified() {
        return $this->verified;
    }

    public function getSuperAdmin() {
        return $this->superAdmin;
    }

    public function getCallsign() {
        return $this->callsign;
    }

    public function getPosition() {
        return $this->position;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getPicture() {
        return $this->picture;
    }

    public function getCalendarGroundschool() {
        return $this->calendarGroundschool;
    }

    public function getCalendarZeusUsername() {
        return $this->calendarZeusUsername;
    }

    public function getNotificationZeus() {
        return $this->notification_zeus;
    }

    public function getNotificationNews() {
        return $this->notification_news;
    }

    public function getNotificationFtebay() {
        return $this->notification_ftebay;
    }

    public function hasPermission($permissionName)  {
        if (array_key_exists($permissionName, $this->userPermissions) && (int) $this->userPermissions[$permissionName] === 1) {
            return true;
        }
        else if($this->superAdmin) {
            return true;
        }
        else {
            for ($i = 0; $i < count($this->groups); $i++) {
                if (array_key_exists($permissionName, $this->groups[$i]) && (int) $this->groups[$i][$permissionName] === 1) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getGraduated(){
        for ($i = 0; $i < count($this->groups); $i++) {
            if ( (int) $this->groups[$i]["active"] == 1) {
                return true;
            }
        }
        return false;
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function generateSalt() {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(8/strlen($x)) )),1,8);
    }
}
