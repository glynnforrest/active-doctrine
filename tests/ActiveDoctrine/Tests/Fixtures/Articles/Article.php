<?php

namespace ActiveDoctrine\Tests\Fixtures\Articles;

use ActiveDoctrine\Entity\Entity;
use ActiveDoctrine\Entity\Traits\TimestampTrait;

/**
 * Article
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Article extends Entity
{
    use TimestampTrait;

    protected static $table = 'articles';
    protected static $fields = [
        'id',
        'title',
        'created_at',
        'updated_at',
    ];
    protected static $types = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
