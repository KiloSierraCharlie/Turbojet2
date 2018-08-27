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

    public function getUserEvents($zeusUsername) {
        $queryBuilder = $this->conn->createQueryBuilder();

        $queryBuilder
            ->select('*' )
            ->from('zeus_events_cache')
            ->where('captain = :captain')->setParameter(':captain', $zeusUsername)
            ->orWhere('crew1 = :crew1')->setParameter(':crew1', $zeusUsername)
        ;

        $stmt = $queryBuilder->execute();
        $events = $stmt->fetchAll();

        return $events;
    }

    public function deleteEvents($ids) {
        if(count($ids) === 0) {
            return 0;
        }

        foreach ($ids as $id) {
            $id = str_replace("event_", "", $id);
            $sql .= 'DELETE FROM `zeus_events_cache` WHERE `zeus_events_cache`.`id` = '.$id.'; ';
        }

        return $this->conn->executeUpdate($sql);
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

    public function deleteOldEvents() {
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->delete('zeus_events_cache')
            ->where('DATE(end) <= DATE_SUB(UTC_DATE(), INTERVAL 1 DAY)')
            ->execute()
        ;
    }
}
