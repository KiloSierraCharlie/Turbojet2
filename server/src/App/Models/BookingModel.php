<?php

namespace App\Models;

use Doctrine\DBAL\Connection;

class BookingModel extends AbstractModel {

    public function getBookings($type, $dateFrom, $dateTo) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('b.id', 'b.start', 'b.end', 'b.id_user', 'CONCAT(u.first_name, " ",u.last_name) as user_name', 'b.booking_reason','b.price', 'b.paid', 'b.id_resource', 'br.name as resource_name')
                ->from('bookings', 'b')
                ->innerJoin('b', 'users', 'u', 'b.id_user = u.id')
                ->innerJoin('b', 'booking_resources', 'br', 'b.id_resource = br.id')
                ->where('b.cancelled = 0')
                ->andWhere('b.type = :type')->setParameter('type', $type)
                ->andWhere('b.start >= :start')->setParameter('start', $dateFrom)
                ->andWhere('b.end <= :end')->setParameter('end', $dateTo)
            ;

            $stmt = $queryBuilder->execute();
            $bookings = $stmt->fetchAll();

            return $bookings;
        }
        catch(\Exception $e) {
            return $e;
        }
    }

    public function getResources($type) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('id', 'name')
                ->from('booking_resources')
                ->where('type = :type')->setParameter('type', $type)
            ;

            $stmt = $queryBuilder->execute();
            $resources = $stmt->fetchAll();

            return $resources;
        }
        catch(\Exception $e) {
            return $e;
        }
    }

    public function getBookingAuthorId($bookingId) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->select('id_user')
                ->from('bookings')
                ->where('id = :id')->setParameter(':id', $bookingId);
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

    // public function getMinivanBooking($idBooking) {
    //     try {
    //         $queryBuilder = $this->conn->createQueryBuilder();
    //
    //         $queryBuilder
    //             ->select('b.id', 'b.start', 'b.end', 'b.price', 'b.paid', 'b.booking_reason', 'b.id_user', 'CONCAT(u.first_name, " ",u.last_name) as user_name', 'b.id_resource', 'br.name as resource_name')
    //             ->from('bookings', 'b')
    //             ->innerJoin('b', 'users', 'u', 'b.id_user = u.id')
    //             ->innerJoin('b', 'booking_resources', 'br', 'b.id_resource = br.id')
    //             ->where('b .cancelled = 0')
    //             ->andWhere('b.type = :type')->setParameter('type', $type)
    //             ->andWhere('b.from >= :from')->setParameter('from', $dateFrom)
    //             ->andWhere('b.to <= :to')->setParameter('to', $dateTo)
    //         ;
    //
    //         $stmt = $queryBuilder->execute();
    //         $bookings = $stmt->fetch();
    //
    //         return $booking;
    //     }
    //     catch(\Exception $e) {
    //         return false;
    //     }
    // }

    public function add($type, $start, $end, $bookingReason, $userId, $price, $resourceId) {
        try {
            // Edit the document
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->insert('bookings')
                ->setValue('start', ':start')->setParameter(':start', $start)
                ->setValue('end', ':end')->setParameter(':end', $end)
                ->setValue('booking_reason', ':reason')->setParameter(':reason', $bookingReason)
                ->setValue('id_user', ':user')->setParameter(':user', $userId)
                ->setValue('type', ':type')->setParameter(':type', $type)
                ->setValue('cancelled', '0')
                ->setValue('date_created', 'NOW()')
                ->setValue('paid', '0')
            ;

            if($resourceId) {
                $queryBuilder->setValue('id_resource', ':resource')->setParameter(':resource', $resourceId);
            }

            if($price !== -1){
                $queryBuilder->setValue('price', ':price')->setParameter(':price', $price);
            }

            $queryBuilder->execute();

            return true;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function edit($id, $bookingReason) {
        try {
            // Edit the document
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->update('bookings')
                ->set('booking_reason', ':reason')->setParameter(':reason', $bookingReason)
                ->where('id = :id')->setParameter(':id', $id)
                ->execute();

            return true;
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function changeState($id, $isCancelled) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->update('bookings')
                ->set('cancelled', ':cancelled')->setParameter(':cancelled', $isCancelled)
                ->where('id = :id')->setParameter(':id', $id)
                ->execute()
            ;

            return true;
        }
        catch(\Exception $e) {
            return $e;
        }
    }

    public function markAsPaid($id) {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();

            $queryBuilder
                ->update('bookings')
                ->set('paid', '1')
                ->where('id = :id')->setParameter(':id', $id)
                ->execute()
            ;

            return true;
        }
        catch(\Exception $e) {
            return $e;
        }
    }
}
