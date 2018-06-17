<?php

namespace App\Models;

use Doctrine\DBAL\Connection;
// use Doctrine\DBAL\ParameterType;
// use Doctrine\DBAL\Types;

class EditorialContentModel extends AbstractModel {

    /**
    * TODO
    *
    * @return array An array with all the news found
    */
    public function getNews($from = -1, $length = -1) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            if($from !== -1  && $length !== -1) {
                $queryBuilder->setFirstResult($from)->setMaxResults($length);
            }

            $queryBuilder
                ->select('e.id', 'e.title', 'e.content', 'e.id_user', 'CONCAT(u.first_name, " ",u.last_name) as name', 'e.date')
                ->from('editorial_contents', 'e')
                ->innerJoin('e', 'users', 'u', 'u.id = e.id_user')
                ->where('e.active = 1')
                ->andWhere('e.type = "news"')
                ->orderBy('e.date', 'desc')
            ;

            $stmt = $queryBuilder->execute();
            $news = $stmt->fetchAll();

            $queryBuilder = $this->conn->createQueryBuilder();
            $queryBuilder
                ->select('COUNT(*) as total')
                ->from('editorial_contents')
                ->where('active = 1')
                ->andWhere('type = "news"')
            ;
            $stmt = $queryBuilder->execute();
            $total = $stmt->fetchColumn(0);

            return array('news' => $news, 'length' => ($length !== -1 ? $length : -1), 'currentPage' => ($from !== -1 ? $from : 0), 'totalPages' => ceil($total / $length));
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function getFTEbayOffers() {
        // try {
            $queryBuilder = $this->conn->createQueryBuilder();

            // TODO "is more than ?"
            // TODO expiry date bof, je prererai un calcul dynamique basé sur la date actuelle

            $queryBuilder
                ->select('f.id', 'f.title', 'f.content', 'f.id_user', 'CONCAT(u.first_name, " ",u.last_name) as name', 'f.date')
                ->from('editorial_contents', 'f')
                ->innerJoin('f', 'users', 'u', 'u.id = f.id_user')
                ->where('f.active = 1')
                ->andWhere('f.type = "ftebay"')
                ->andWhere('f.expiry_date > NOW()')
                ->orderBy('f.date', 'desc')
            ;

            $stmt = $queryBuilder->execute();
            $posts = $stmt->fetchAll();

            return $posts;
        // }
        // catch(\Exception $e) {
        //     return false;
        // }
    }

    public function addFTEbayOffer($userId, $content, $title) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();


            $queryBuilder->insert('editorial_contents');
            $queryBuilder->setValue('content', ':content')->setParameter(':content', $content);
            $queryBuilder->setValue('title', ':title')->setParameter(':title', $title);
            $queryBuilder->setValue('id_user', ':idUser')->setParameter(':idUser', $userId);
            $queryBuilder->setValue('active', '1');
            $queryBuilder->setValue('type', '"ftebay"');
            $queryBuilder->setValue('date', 'NOW()');
            $queryBuilder->setValue('expiry_date', 'NOW() + INTERVAL 14 DAY');

            $queryBuilder->execute();
            return true;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function editFTEbayOffer($offerId, $content, $title) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->update('editorial_contents')
                ->set('title', ':title')->setParameter(':title', $title)
                ->set('content', ':content')->setParameter(':content', $content)
                ->where('id = :id')->setParameter(':id', $offerId)
                ->execute()
            ;

            return true;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function deleteFTEbayOffer($offerId) {
        try {
            // Delete item from database
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->delete('editorial_contents')
                ->where('id = :id')->setParameter(':id', $offerId)
                ->execute()
            ;

            return true;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function getFTEbayOfferAuthorId($offerId) {
        try {
            // Delete item from database
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('id_user')
                ->from('editorial_contents')
                ->where('id = :id')->setParameter(':id', $offerId);
            ;

            $stmt = $queryBuilder->execute();
            $result = $stmt->fetch();
            $authorId = $result['id_user'];
            return $authorId;
        }
        catch(\Exception $e) {
            return false;
        }
    }
}
