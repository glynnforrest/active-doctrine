<?php

namespace ActiveDoctrine\Tests\Functional\Traits;

use ActiveDoctrine\Tests\Functional\FunctionalTestCase;
use ActiveDoctrine\Tests\Fixtures\Articles\Article;
use ActiveDoctrine\Tests\Fixtures\Articles\Writer;

/**
 * TimestampTraitTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TimestampTraitTest extends FunctionalTestCase
{
    public function setup()
    {
        $this->loadSchema('articles');
    }

    public function testAddTimestampsOnInsert()
    {
        $article = new Article($this->getConn());
        $article->title = 'Foo';
        $article->save();
        $this->assertEquals(new \DateTime(), $article->created_at);
        $this->assertEquals(new \DateTime(), $article->updated_at);
    }
}
