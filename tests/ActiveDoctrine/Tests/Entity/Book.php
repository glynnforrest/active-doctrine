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

    protected static $table = 'books';
    protected static $fields = [
        'id',
        'name',
        'description',
        'author_id'
    ];
    protected static $relations = [
        'author' => ['has_one', 'ActiveDoctrine\Tests\Entity\Author', 'author_id', 'author']
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
