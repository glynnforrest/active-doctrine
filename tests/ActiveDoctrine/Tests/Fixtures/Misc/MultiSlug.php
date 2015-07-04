<?php

namespace ActiveDoctrine\Tests\Fixtures\Misc;

use ActiveDoctrine\Entity\Entity;
use ActiveDoctrine\Entity\Traits\SlugTrait;

/**
 * MultiSlug
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MultiSlug extends Entity
{
    use SlugTrait;

    protected static $table = 'multi_slug';
    protected static $fields = [
        'id',
        'foo',
        'foo_slug',
        'bar',
        'bar_slug',
        'baz',
        'baz_slug',
    ];
    protected static $slugs = [
        'foo' => 'foo_slug',
        'bar' => 'bar_slug',
        'baz' => 'baz_slug',
    ];
}
