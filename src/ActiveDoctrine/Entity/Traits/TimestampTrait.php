<?php

namespace ActiveDoctrine\Entity\Traits;

use \DateTime;

/**
 * TimestampTrait
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
trait TimestampTrait
{
    protected static $timestamps = [
        'created_at' => 'insert',
        'updated_at' => 'update',
    ];

    protected static function initTimestampTrait()
    {
        static::addEventCallBack('insert', function($entity) {
            foreach (static::$timestamps as $field => $event) {
                if (!$entity->getRaw($field)) {
                    $entity->setRaw($field, new DateTime());
                }
            }
        });

        static::addEventCallBack('update', function($entity) {
            if (!$entity->updated_at) {
                $entity->updated_at = new DateTime();
            }
        });
    }
}
