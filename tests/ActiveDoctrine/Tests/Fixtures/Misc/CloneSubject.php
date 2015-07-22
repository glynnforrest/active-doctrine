<?php

namespace ActiveDoctrine\Tests\Fixtures\Misc;

use ActiveDoctrine\Entity\Entity;

/**
 * CloneSubject
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CloneSubject extends Entity
{
    protected static $fields = [
        'datetime'
    ];
    protected static $types = [
        'datetime' => 'datetime'
    ];

    public function __clone()
    {
        parent::__clone();
        //make a clone of datetime
        $this->datetime = clone $this->datetime;
    }
}
