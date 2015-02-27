<?php

namespace ActiveDoctrine\Tests\Fixtures\Articles;

use ActiveDoctrine\Entity\Entity;
use ActiveDoctrine\Entity\Traits\TimestampTrait;
use ActiveDoctrine\Entity\Traits\SlugTrait;

/**
 * Article
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Article extends Entity
{
    use TimestampTrait;
    use SlugTrait;

    protected static $table = 'articles';
    protected static $fields = [
        'id',
        'title',
        'slug',
        'created_at',
        'updated_at',
    ];
    protected static $types = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
