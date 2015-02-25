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

    public function testAddTimestampsForDifferentEntities()
    {
        $article = new Article($this->getConn());
        $article->title = 'Foo';
        $article->save();
        $writer = new Writer($this->getConn());
        $writer->save();

        $datetime = new \DateTime();
        $this->assertEquals($datetime, $article->created_at);
        $this->assertEquals($datetime, $article->updated_at);

        $this->assertEquals($datetime, $writer->createdAt);
        $this->assertEquals($datetime, $writer->anotherCreate);
        $this->assertEquals($datetime, $writer->updatedAt);
        $this->assertEquals($datetime, $writer->anotherUpdate);

        //check that other fields haven't been set on different entities
        $this->assertNull($writer->created_at);
        $this->assertNull($writer->updated_at);

        $this->assertNull($article->createdAt);
        $this->assertNull($article->anotherCreate);
        $this->assertNull($article->updatedAt);
        $this->assertNull($article->anotherUpdate);
    }
}
