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
        $this->title = $user['title'];
        $this->phone = $user['phone'];
        $this->graduated = (int) $user['graduated'];
        $this->calendarGroundschool = $user['calendar_groundschool'];
        $this->calendarZeusUsername = $user['calendar_zeus_username'];
        $this->calendarEnableEmailNotifications = $user['calendar_enable_email_notifications'];
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

    public function getTitle() {
        return $this->title;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getGraduated() {
        return $this->graduated;
    }

    public function getCalendarGroundschool() {
        return $this->calendarGroundschool;
    }

    public function getCalendarZeusUsername() {
        return $this->calendarZeusUsername;
    }

    public function getCalendarEnableEmailNotifications() {
        return $this->calendarEnableEmailNotifications;
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
}
