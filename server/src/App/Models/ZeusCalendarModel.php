<?php

namespace App\Models;

use Doctrine\DBAL\Connection;

class ZeusCalendarModel extends AbstractModel {

    public function getAllEvents() {
        $queryBuilder = $this->conn->createQueryBuilder();

        $queryBuilder
            ->select('*' )
            ->from('zeus_events_cache')
        ;

        $stmt = $queryBuilder->execute();
        $events = $stmt->fetchAll();

        return $events;
    }

    public function deleteEvents($ids) {
        if(count($ids) === 0) {
            return 0;
        }

        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->delete('zeus_events_cache')
            ->where('id IN (:ids)')->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY)
            ->execute()
        ;

        return 1;
    }

    public function updateEvents($ids, $events) {
        if(count($ids) === 0) {
            return 0;
        }

        $sql = '';
        foreach ($ids as $id) {
            $sql .= 'UPDATE zeus_events_cache SET start="'.$events[$id]["start"].'", end="'.$events[$id]["end"].'", captain="'.$events[$id]["captain"].'", crew1="'.$events[$id]["crew1"].'", exercise_title="'.$events[$id]["exercise_title"].'", registration="'.$events[$id]["registration"].'" WHERE id = "'.$events[$id]["id"].'";';
        }

        return $this->conn->executeUpdate($sql);
    }

    public function insertEvents($ids, $events) {
        if(count($ids) === 0) {
            return 0;
        }

        $sql = '';
        foreach ($ids as $id) {
            $sql .= 'INSERT INTO zeus_events_cache(id, start, end, captain, crew1, exercise_title, registration) VALUES ("'.$events[$id]["id"].'", "'.$events[$id]["start"].'", "'.$events[$id]["end"].'", "'.$events[$id]["captain"].'", "'.$events[$id]["crew1"].'", "'.$events[$id]["exercise_title"].'", "'.$events[$id]["registration"].'");';
        }

        return $this->conn->executeUpdate($sql);
    }

    // public function saveEvents($events) {
    //     // Delete events to be deleted
    //     $idsToDelete = array();
    //     foreach ($events['deletions'] as $entry) {
    //         $idsToDelete[] = $entry['id'];
    //     }
    //
    //     $queryBuilder = $this->conn->createQueryBuilder();
    //     $queryBuilder
    //         ->delete('zeus_events_cache')
    //         ->where('id IN (:ids)')->setParameter('ids', $idsToDelete, Connection::PARAM_STR_ARRAY)
    //         ->execute()
    //     ;
    //
    //     // Insert new lines
    //     $sql = '';
    //     foreach ($events['insertions'] as $eventJson) {
    //         // $event = json_decode($eventJson, TRUE);
    //         $event = $eventJson;
    //         // $sql .= "INSERT INTO zeus_events_cache(" . implode(',', array_keys($event)) . ") VALUES ('".implode("','",array_values($event))."')";
    //         // $sql .= " ON DUPLICATE KEY UPDATE (" . implode(',', array_keys($event)) . ") SET VALUES ('".implode("','",array_values($event))."');";
    //             $sql .= 'INSERT INTO zeus_events_cache(id, start, end, captain, crew1, exercise_title, registration) VALUES ("'.$event['id'].'", "'.$event['start'].'", "'.$event['end'].'", "'.$event['captain'].'", "'.$event['crew1'].'", "'.$event['exercise_title'].'", "'.$event['registration'].'")';
    //             $sql .= ' ON DUPLICATE KEY UPDATE id="'.$event['id'].'", start="'.$event['start'].'", captain="'.$event['captain'].'", crew1="'.$event['crew1'].'", exercise_title="'.$event['exercise_title'].'", registration="'.$event['registration'].'";';
    //
    //     }
    //
    //     $count = $this->conn->executeUpdate($sql);
    // }
    //
    // public function edit($id, $name, $fileName, $link) {
    //     try {
    //         // Get type and filename for later deletion
    //         $queryBuilder = $this->conn->createQueryBuilder();
    //         $queryBuilder
    //             ->select('type', 'path' )
    //             ->from('documents')
    //             ->where('id = :id')->setParameter(':id', $id)
    //         ;
    //
    //         $stmt = $queryBuilder->execute();
    //         $result = $stmt->fetch();
    //         $oldFileName = $result['path'];
    //         $type = $result['type'];
    //
    //         // Edit the document
    //         $queryBuilder = $this->conn->createQueryBuilder();
    //
    //         $queryBuilder->update('documents');
    //         $queryBuilder->set('name', ':name')->setParameter(':name', $name);
    //         // $queryBuilder->set('id_document_collection', ':collection')->setParameter(':collection', $collectionId);
    //
    //         if($fileName && !$link) {
    //             $queryBuilder->set('path', ':path')->setParameter(':path', $fileName);
    //         }
    //         else if(!$fileName && $link) {
    //             $queryBuilder->set('path', ':path')->setParameter(':path', $link);
    //         }
    //
    //         $queryBuilder->set('date_modified', 'NOW()');
    //         $queryBuilder->where('id = :id')->setParameter(':id', $id);
    //
    //         $queryBuilder->execute();
    //         return $type === 'document' ? $oldFileName : NULL;
    //     }
    //     catch(\Exception $e) {
    //         return false;
    //     }
    // }

    public function deleteOldEvents() {
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->delete('zeus_events_cache')
            ->where('DATE(end) <= DATE_SUB(UTC_DATE(), INTERVAL 1 DAY)')
            ->execute()
        ;
    }
}
