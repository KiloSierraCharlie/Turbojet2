<?php

namespace App\Models;

use Doctrine\DBAL\Connection;

class EditorialContentModel extends AbstractModel {

    /**
    * TODO
    *
    * @return array An array with all the news found
    */
    public function getPosts($type = 'news', $from = -1, $length = -1) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            if($from !== -1  && $length !== -1) {
                $queryBuilder->setFirstResult($from)->setMaxResults($length);
            }

            $queryBuilder
                ->select('e.id', 'e.title', 'e.content', 'e.id_user', 'CONCAT(u.first_name, " ",u.last_name) as name', 'e.date', 'e.type', 'e.menu_icon')
                ->from('editorial_contents', 'e')
                ->innerJoin('e', 'users', 'u', 'u.id = e.id_user')
                ->where('e.active = 1')
                ->where('e.type = :type')->setParameter(':type', $type)
            ;

            if($type === 'ftebay') {
                $queryBuilder->andWhere('e.expiry_date > NOW()');
            }

            $queryBuilder->orderBy('e.date', 'desc');


            $stmt = $queryBuilder->execute();
            $posts = $stmt->fetchAll();

            // Pagination management
            if($from !== -1 && $length !== -1) {
                $queryBuilder = $this->conn->createQueryBuilder();
                $queryBuilder
                    ->select('COUNT(*) as total')
                    ->from('editorial_contents')
                    ->where('active = 1')
                    ->andWhere('type = :type')->setParameter(':type', $type);
                ;

                $stmt = $queryBuilder->execute();
                $total = $stmt->fetchColumn(0);

                return array('posts' => $posts, 'length' => ($length !== -1 ? $length : -1), 'currentPage' => ($from !== -1 ? $from : 0), 'totalPages' => ceil($total / $length));
            }
            else {
                return array('posts' => $posts);
            }
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function getPost($type, $postId) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('id', 'title', 'content', 'type')
                ->from('editorial_contents')
                ->where('active = 1')
                ->andWhere('id = :id')->setParameter(':id', $postId)
            ;

            $stmt = $queryBuilder->execute();
            $page = $stmt->fetch();

            return $page;
        }
        catch(\Exception $e) {
            return false;
        }
    }


    public function addPost($type = 'news', $userId, $content, $title) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->insert('editorial_contents')
                ->setValue('content', ':content')->setParameter(':content', $content)
                ->setValue('title', ':title')->setParameter(':title', $title)
                ->setValue('id_user', ':idUser')->setParameter(':idUser', $userId)
                ->setValue('active', '1')
                ->setValue('type', ':type')->setParameter(':type', $type)
                ->setValue('date', 'NOW()')
            ;

            if($type === 'ftebay') {
                $queryBuilder->setValue('expiry_date', 'NOW() + INTERVAL 14 DAY');
            }

            $queryBuilder->execute();
            return true;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function editPost($postId, $content, $title) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->update('editorial_contents')
                ->set('title', ':title')->setParameter(':title', $title)
                ->set('content', ':content')->setParameter(':content', $content)
                ->where('id = :id')->setParameter(':id', $postId)
                ->execute()
            ;

            return true;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function deletePost($postId) {
        try {
            // Delete item from database
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->delete('editorial_contents')
                ->where('id = :id')->setParameter(':id', $postId)
                ->execute()
            ;

            return true;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function getFTEbayPostAuthorId($postId) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('id_user')
                ->from('editorial_contents')
                ->where('id = :id')->setParameter(':id', $postId);
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
