<?php

namespace App\Models;

use Doctrine\DBAL\Connection;

class MenuModel extends AbstractModel {

    public function getDynamicPages($pagesTypes) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('id', 'title', 'type')
                ->from('editorial_contents')
                ->where('active = 1')
                ->andWhere('type IN (:types)')->setParameter('types', $pagesTypes, Connection::PARAM_STR_ARRAY)
            ;

            $stmt = $queryBuilder->execute();
            $pages = $stmt->fetchAll();

            return $pages;
        }
        catch(\Exception $e) {
            return $e;
        }
    }
}
