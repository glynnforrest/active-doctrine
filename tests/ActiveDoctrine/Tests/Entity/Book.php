<?php

namespace ActiveDoctrine\Tests\Entity;

use ActiveDoctrine\Entity\Entity;

/**
 * Book
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Book extends Entity
{

    protected static $fields = [
        'id',
        'name',
        'author'
    ];

    public function setName($name)
    {
        return strtoupper($name);
    }

    public function getAuthor()
    {
        return strtoupper($this->getRaw('author'));
    }

}
