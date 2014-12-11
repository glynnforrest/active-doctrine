<?php

namespace ActiveDoctrine\Tests\Fixtures\SetterGetter;

use ActiveDoctrine\Entity\Entity;

/**
 * UpperCase
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class UpperCase extends Entity
{
    protected static $fields = [
        'name',
        'description',
    ];

    public function setterName($name)
    {
        return strtoupper($name);
    }

    public function getterDescription()
    {
        return strtoupper($this->getRaw('description'));
    }
}
