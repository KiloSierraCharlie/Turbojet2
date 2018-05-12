<?php

namespace App\Models;

use Doctrine\DBAL\Connection;

class GroupModel extends AbstractModel {
    public function getGroups($nonActive = false) {
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
}
