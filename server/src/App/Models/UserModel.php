<?php

namespace App\Models;

use Doctrine\DBAL\Connection;
// use Doctrine\DBAL\ParameterType;
// use Doctrine\DBAL\Types;

class UserModel extends AbstractModel {

    /**
    * TODO
    *
    * @return array An array with all the users found
    */
    public function getUsers($groupId, $includeGraduated = false) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('u.id', 'u.first_name', 'u.last_name', 'u.picture', 'u.position', 'u.graduated', 'GROUP_CONCAT(CONCAT(g.id, "::", g.name, "::", g.type)) as groups')
                ->from('users', 'u')
                ->innerJoin('u', 'group_membership', 'gm', 'u.id = gm.id_user')
                ->innerJoin('gm', 'groups', 'g', 'gm.id_group = g.id')
                ->where('u.banned = 0')
                ->andWhere('u.verified = 1')
                ->groupBy('u.id')
            ;

            if(!$includeGraduated) {
                $queryBuilder->andWhere('u.graduated = 0');
            }

            $stmt = $queryBuilder->execute();
            $users = $stmt->fetchAll();

            // Format groups in name/type key/value array
            foreach ($users as &$user) {
                $tempGroups = $user['groups'] = explode(',', $user['groups']);
                $user['groups'] = array();

                foreach ($tempGroups as &$group) {
                    $user['groups'][] = array('id' => explode('::', $group)[0],'name' => explode('::', $group)[1], 'type' => explode('::', $group)[2]);
                }
            }

            return $users;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function createUser($username, $firstName, $lastName, $room, $group, $phone, $picture) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder->insert('users');
            $queryBuilder->setValue('email', ':email')->setParameter(':email', $username);
            $queryBuilder->setValue('first_name', ':firstName')->setParameter(':firstName', $firstName);
            $queryBuilder->setValue('last_name', ':lastName')->setParameter(':lastName', $lastName);
            $queryBuilder->setValue('room', ':room')->setParameter(':room', $room);
            $queryBuilder->setValue('phone', ':phone')->setParameter(':phone', $phone);
            $queryBuilder->setValue('picture', ':picture')->setParameter(':picture', $picture);
            $queryBuilder->execute();
            $id = $this->conn->lastInsertId();

            $queryBuilder = $this->conn->createQueryBuilder();
            $queryBuilder->insert('group_membership');
            $queryBuilder->setValue('id_user', ':idUser')->setParameter(':idUser', $id);
            $queryBuilder->setValue('id_group', ':idGroup')->setParameter(':idGroup', $group);
            $queryBuilder->execute();

            return true;
        }
        catch(\Exception $e) {
            return $e;
        }
    }

    public function setUserPassword($id, $salt, $encodedPassword) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder->update('users');
            $queryBuilder->set('salt', ":salt")->setParameter('salt', $salt);
            $queryBuilder->set('password', ":password")->setParameter('password', $encodedPassword);
            $queryBuilder->where('id = :id')->setParameter('id', $id);
            $queryBuilder->execute();

            return true;
        }
        catch(\Exception $e) {
            return $e;
        }
    }

    /**
    * TODO
    *
    * @return array The details of the user
    */
    public function getUserDetails($id) {
        // TODO Bools are returned as boolean. Caution, if changed, think to change the mapping in openUserDialog in UserDetailsDialog
        $queryBuilder = $this->conn->createQueryBuilder();

        $queryBuilder
            ->select('u.id', 'u.first_name', 'u.last_name', 'u.email', 'u.room', 'u.callsign',
                'u.position', 'u.phone', 'u.picture', 'u.graduated', 'GROUP_CONCAT(CONCAT(g.id, "::", g.name, "::", g.type)) as groups',
                'u.calendar_zeus_username', 'u.notification_zeus', 'u.notification_news', 'u.notification_ftebay')
            ->from('users', 'u')
            ->innerJoin('u', 'group_membership', 'gm', 'u.id = gm.id_user')
            ->innerJoin('gm', 'groups', 'g', 'gm.id_group = g.id')
            ->where('u.id = :id')->setParameter(':id', $id)
        ;

        $stmt = $queryBuilder->execute();
        $user = $stmt->fetch();

        $tempGroups = $user['groups'] = explode(',', $user['groups']);
        $user['groups'] = array();

        foreach ($tempGroups as &$group) {
            $user['groups'][] = array('id' => explode('::', $group)[0],'name' => explode('::', $group)[1], 'type' => explode('::', $group)[2]);
        }

        return $user;
    }

    public function editUserDetails($id, $userData) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder->update('users');

            foreach ($userData as $key => $value) {
                $queryBuilder->set($key, ":$key")->setParameter($key, $value);
            }

            $queryBuilder->where('id = :id')->setParameter('id', $id);
            $queryBuilder->execute();
            return $this->getUserDetails($id);
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function setGroups($id, $groups) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder->delete('group_membership');
            $queryBuilder->where('id_user = :id')->setParameter('id', $id);
            $queryBuilder->execute();

            for ($i = 0; $i < count($groups); $i++) {
                $queryBuilder->insert('group_membership');
                $queryBuilder->setValue('id_user', ':id_user')->setParameter(':id_user', $id);
                $queryBuilder->setValue('id_group', ':id_group')->setParameter(':id_group', (int)$groups[$i]);
                $queryBuilder->execute();
            }

            return true;
        }
        catch(\Exception $e) {
            return $e;
        }
    }

    public function getAllGroups($nonActive = false) {
        $groups = $this->conn->fetchAll('
            SELECT
                id,
                category,
                name,
                type
            FROM groups
            WHERE active = ?
        ', array(!$nonActive));

        return $groups;
    }

    public function getGroupsKeyValue() {
        $groups = $this->conn->fetchAll('
            SELECT
                id as value,
                name as text
            FROM groups
            WHERE active = 1
        ');

        return $groups;
    }

    public function getUserFromZeusUsername($username) {
        $queryBuilder = $this->conn->createQueryBuilder();

        $queryBuilder
            ->select('email')
            ->from('users')
            ->where('calendar_zeus_username = :username')->setParameter(':username', $username);
        ;

        $stmt = $queryBuilder->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $users;
    }

    public function getAdminUsers() {
        $queryBuilder = $this->conn->createQueryBuilder();

        $queryBuilder
            ->select('email')
            ->from('users')
            ->where('super_admin = 1')
        ;

        $stmt = $queryBuilder->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $users;
    }

    public function getFtebaySubscriptionUsers() {
        $queryBuilder = $this->conn->createQueryBuilder();

        $queryBuilder
            ->select('email')
            ->from('users')
            ->where('notification_ftebay = 1')
            ->andWhere('active = 1')
            ->andWhere('banned = 0')
        ;

        $stmt = $queryBuilder->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $users;
    }

    public function getNewsSubscriptionUsers() {
        $queryBuilder = $this->conn->createQueryBuilder();

        $queryBuilder
            ->select('email')
            ->from('users')
            ->where('notification_news = 1')
            ->andWhere('active = 1')
            ->andWhere('banned = 0')
        ;

        $stmt = $queryBuilder->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $users;
    }
}
