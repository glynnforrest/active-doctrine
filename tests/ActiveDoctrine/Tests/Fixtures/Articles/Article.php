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
}
