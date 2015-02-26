<?php

namespace ActiveDoctrine\Entity\Traits;

use DateTime;

/**
 * TimestampTrait
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
trait TimestampTrait
{
    /**
     * configure the timestamps with $insert_timestamps and
     * $update_timestamps
     *
     * protected static $insert_timestamps = [
     *      'createdAt',
     *      'timeCreated',
     * ];
     * OR
     * protected static $insert_timestamps = 'createdAt';
     *
     * protected static $update_timestamps = [
     *      'updatedAt',
     *      'timeUpdated',
     * ];
     * OR
     * protected static $update_timestamps = 'updatedAt';
     */
    protected static function initTimestampTrait()
    {
        $update_timestamps = isset(static::$update_timestamps) ? (array) static::$update_timestamps : ['updated_at'];

        $insert_timestamps = isset(static::$insert_timestamps) ? static::$insert_timestamps : ['created_at'];
        $insert_timestamps = array_merge($update_timestamps, $insert_timestamps);

        static::addEventCallBack('insert', function ($entity) use ($insert_timestamps) {
            foreach ($insert_timestamps as $field) {
                if (!$entity->getRaw($field)) {
                    $entity->setRaw($field, new DateTime());
                }
            }
        });

        static::addEventCallBack('update', function ($entity) use ($update_timestamps) {
            if ($entity->isModified()) {
                foreach ($update_timestamps as $field) {
                    $entity->setRaw($field, new DateTime());
                }
            }
        });
    }
}
