<?php

namespace App\Models;

use Doctrine\DBAL\Connection;
// use Doctrine\DBAL\ParameterType;
// use Doctrine\DBAL\Types;

class DocumentModel extends AbstractModel {

    /**
    * TODO
    *
    */
    public function getDocumentsFromCollection($collectionSlug) {

        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('d.id', 'd.date_created', 'd.date_modified', 'd.name', 'd.type', 'd.path', 'dc.name as collection' )
                ->from('documents', 'd')
                ->innerJoin('d', 'document_collections', 'dc', 'dc.id = d.id_document_collection')
                ->where('d.active = 1')
                ->andWhere('dc.slug = :slug')
                ->setParameter('slug', $collectionSlug)
            ;

            $stmt = $queryBuilder->execute();
            $documents = $stmt->fetchAll();

            // Format groups in name/type key/value array
            // foreach ($users as &$user) {
            //     $tempGroups = $user['groups'] = explode(',', $user['groups']);
            //     $user['groups'] = array();
            //
            //     foreach ($tempGroups as &$group) {
            //         $user['groups'][] = array('name' => explode('::', $group)[0], 'type' => explode('::', $group)[1]);
            //     }
            // }
            return $documents;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function getCollectionIdFromSlug($collectionSlug) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('id')
                ->from('document_collections')
                ->where('slug = :slug')->setParameter('slug', $collectionSlug)
            ;

            $stmt = $queryBuilder->execute();
            $collectionId = $stmt->fetchColumn();

            return $collectionId;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function add($name, $collectionId, $fileName, $link) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder->insert('documents');
            $queryBuilder->setValue('name', ':name')->setParameter(':name', $name);
            $queryBuilder->setValue('id_document_collection', ':collection')->setParameter(':collection', $collectionId);

            if($fileName && !$link) {
                $queryBuilder->setValue('path', ':path')->setParameter(':path', $fileName);
                $queryBuilder->setValue('type', '"document"');
            }
            else if(!$fileName && $link) {
                $queryBuilder->setValue('path', ':path')->setParameter(':path', $link);
                $queryBuilder->setValue('type', '"link"');
            }

            $queryBuilder->setValue('active', '1');
            $queryBuilder->setValue('date_created', 'NOW()');
            $queryBuilder->setValue('date_modified', 'NOW()');

            $queryBuilder->execute();
            return true;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function edit($id, $name, $fileName, $link) {
        try {
            // Get type and filename for later deletion
            $queryBuilder = $this->conn->createQueryBuilder();
            $queryBuilder
                ->select('type', 'path' )
                ->from('documents')
                ->where('id = :id')->setParameter(':id', $id)
            ;

            $stmt = $queryBuilder->execute();
            $result = $stmt->fetch();
            $oldFileName = $result['path'];
            $type = $result['type'];

            // Edit the document
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder->update('documents');
            $queryBuilder->set('name', ':name')->setParameter(':name', $name);
            // $queryBuilder->set('id_document_collection', ':collection')->setParameter(':collection', $collectionId);

            if($fileName && !$link) {
                $queryBuilder->set('path', ':path')->setParameter(':path', $fileName);
            }
            else if(!$fileName && $link) {
                $queryBuilder->set('path', ':path')->setParameter(':path', $link);
            }

            $queryBuilder->set('date_modified', 'NOW()');
            $queryBuilder->where('id = :id')->setParameter(':id', $id);

            $queryBuilder->execute();
            return $type === 'document' ? $oldFileName : NULL;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function delete($id) {
        try {
            // Get type and filename for later deletion
            $queryBuilder = $this->conn->createQueryBuilder();
            $queryBuilder
                ->select('type', 'path' )
                ->from('documents')
                ->where('id = :id')->setParameter(':id', $id)
            ;

            $stmt = $queryBuilder->execute();
            $result = $stmt->fetch();
            $fileName = $result['path'];
            $type = $result['type'];

            // Delete item from database
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder->delete('documents');
            $queryBuilder->where('id = :id')->setParameter(':id', $id);
            $queryBuilder->execute();
            return $type === 'document' ? $fileName : NULL;
        }
        catch(\Exception $e) {
            return false;
        }
    }
}
