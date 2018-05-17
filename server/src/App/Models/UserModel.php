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
        // TODO
        // $filePath = '../assets/profile_pictures/';
        // $files = scandir($filePath);
        //
        // foreach ($files as $key => $value) {
        //     $queryBuilder = $this->conn->createQueryBuilder();
        //     $queryBuilder
        //         ->update('users')
        //         ->set('picture', ':picture')->setParameter('picture', $value)
        //         ->where('id = :id')->setParameter('id', str_replace('.jpg', '', $value))
        //         ->execute();
        // }
        // exit();

        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('u.id', 'u.first_name', 'u.last_name', 'u.picture', 'u.title', 'u.graduated', 'GROUP_CONCAT(CONCAT(g.name, "::", g.type)) as groups')
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
                    $user['groups'][] = array('name' => explode('::', $group)[0], 'type' => explode('::', $group)[1]);
                }
            }
            return $users;
        }
        catch(\Exception $e) {
            return false;
        }
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
