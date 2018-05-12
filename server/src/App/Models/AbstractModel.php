<?php

namespace App\Models;

use Doctrine\DBAL\Connection;

class AbstractModel {
    protected $conn;

    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }
}
