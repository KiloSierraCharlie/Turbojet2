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
        $users = $this->conn->fetchAll('
            SELECT
                u.id,
                u.first_name,
                u.last_name,
                u.title,
                u.graduated
            FROM users u
            JOIN group_membership g ON u.id = g.id_user
            WHERE u.banned = 0 AND u.verified = 1 AND id_group = ? AND u.graduated = ?
        ', array($groupId, $includeGraduated));

        return $users;
    }

    /**
    * TODO
    *
    * @return array The details of the user
    */
    public function getUserDetails($id) {
        $user = $this->conn->fetchAssoc('
            SELECT
                id,
                email,
                first_name,
                last_name,
                room_number,
                callsign,
                title,
                phone,
                graduated
            FROM users
            WHERE id = ?
        ', array($id));

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
}
