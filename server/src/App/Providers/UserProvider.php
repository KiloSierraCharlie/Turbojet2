<?php

namespace App\Providers;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Providers\User;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Doctrine\DBAL\Connection;

class UserProvider implements UserProviderInterface {
    const TOKEN_LIFETIME = 86400; // 24 hours
    protected $conn;


    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }

    /**
    * Search a user by his Authentication token
    * @param string $token
    * @return User The Silex user instance
    */
    public function loadUserByToken($token) {
        $stmt = $this->conn->executeQuery('
            SELECT u.*, t.refreshed_at as tokenRefreshedAt
            FROM users u
            JOIN token t ON t.id_user = u.id
            WHERE t.token = ?
        ', array($token));

        // Check if token exists
        if (!$user = $stmt->fetch()) {
            throw new TokenNotFoundException();
        }

        // Check the token time validity
        $diff = (new \DateTime())->diff(new \DateTime($user['tokenRefreshedAt']));
        $seconds = $diff->days * 24 * 60 * 60;
        $seconds += $diff->h * 60 * 60;
        $seconds += $diff->i * 60;
        $seconds += $diff->s;

        if ($seconds > $this::TOKEN_LIFETIME) {
            //Delete the expired and useless token
            $this->conn->executeUpdate('
                DELETE
                FROM token
                WHERE token = ?
            ', array($token));

            throw new AuthenticationExpiredException();
        }

        $datetime = (new \DateTime())->format('Y-m-d H:i:s');

        // Update the token creation date
        $this->conn->executeUpdate('
            UPDATE token
            SET refreshed_at = ?
            WHERE token = ?
        ', array($datetime, $token));

        // Get user groups
        $stmt = $this->conn->executeQuery('
            SELECT *
            FROM groups g
            JOIN group_membership gm ON g.id = gm.id_group
            WHERE gm.id_user = ?
        ', array(strtolower($user['id'])));

        $groups = $stmt->fetchAll();

        return new User($user['email'], $user['password'], $user['salt'], array(), $user, $groups);
    }

    /**
    * Search a user by his username
    * @param  string $username
    * @return User The Silex user instance
    */
    public function loadUserByUsername($username) {
        $stmt = $this->conn->executeQuery('
            SELECT *
            FROM users
            WHERE email = ?
        ', array(strtolower($username)));

        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        // Get user groups
        $stmt = $this->conn->executeQuery('
            SELECT *
            FROM groups g
            JOIN group_membership gm ON g.id = gm.id_group
            WHERE gm.id_user = ?
        ', array(strtolower($user['id'])));

        $groups = $stmt->fetchAll();

        return new User($user['email'], $user['password'], $user['salt'], array(), $user, $groups);
    }

    /**
    * Search a user by his username
    * @param  string $username
    * @return User The Silex user instance
    */
    public function loadUserById($id) {
        $stmt = $this->conn->executeQuery('
            SELECT *
            FROM users
            WHERE id = ?
        ', array(strtolower($id)));

        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Id "%s" does not exist.', $id));
        }

        // Get user groups
        $stmt = $this->conn->executeQuery('
            SELECT *
            FROM groups g
            JOIN group_membership gm ON g.id = gm.id_group
            WHERE gm.id_user = ?
        ', array(strtolower($user['id'])));

        $groups = $stmt->fetchAll();

        return new User($user['email'], $user['password'], $user['salt'], array(), $user, $groups);
    }

    /**
    * Delete tokens held by a user
    * @param  string $username
    * @return void
    */
    public function deleteUserTokens($username) {
        $this->conn->executeUpdate('
            DELETE t
            FROM token t
            JOIN users u ON t.id_user = u.id
            WHERE u.email = ?
        ', array(strtolower($username)));
    }

    /**
    * Generate an ahthentication token for the user
    * @param  string $username
    * @return string $token The generated token to be used for any API call
    */
    public function generateToken($username) {
        $length = 32; // will generate a 64 chars token
        $token = bin2hex(random_bytes($length));
        $datetime = (new \DateTime())->format('Y-m-d H:i:s');

        $stmt = $this->conn->executeUpdate('
            INSERT INTO token (id_user, token, created_at, refreshed_at)
            VALUES ((SELECT id FROM users WHERE email = ?), ?, ?, ?)
        ', array(strtolower($username), $token, $datetime, $datetime));

            return $token;
        }

        public function refreshUser(UserInterface $user) {
            if (!$user instanceof User) {
                throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
            }

            return $this->loadUserByUsername($user->getUsername());
        }

        public function supportsClass($class) {
            return $class === 'Symfony\Component\Security\Core\User\User';
        }
    }
