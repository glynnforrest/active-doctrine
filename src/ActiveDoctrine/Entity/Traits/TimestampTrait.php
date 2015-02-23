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
    protected static function initTimestampTrait()
    {
        static::addEventCallBack('insert', function($entity) {
            if ($entity->created_at) {
                return;
            }

            $entity->created_at = new DateTime();
        });
    }
}
