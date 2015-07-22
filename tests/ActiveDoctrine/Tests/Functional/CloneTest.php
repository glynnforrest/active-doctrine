<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Tests\Fixtures\MusicFestival\Performance;
use ActiveDoctrine\Tests\Fixtures\Misc\CloneSubject;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class CloneTest extends FunctionalTestCase
{
    public function setUp()
    {
        $this->loadSchema('bookshop');
    }

    public function testClonedEntityCanBeInserted()
    {
        $book = new Book($this->getConn(), [
            'id' => 1,
            'name' => 'title',
            'description' => 'description',
            'authors_id' => 30,
        ]);
        $book->save();

        $copy = clone $book;
        $copy->save();

        $books = Book::select($this->getConn())->execute();
        $this->assertSame(2, count($books));

        $this->assertSame(['1', '2'], $books->getColumn('id'));
    }

    public function testClonedEntityReferencesChildObjects()
    {
        $perf = new Performance($this->getConn(), [
            'name' => 'concert',
            'start_time' => new \DateTime(),
        ]);

        $clone = clone $perf;

        $this->assertSame($perf->start_time, $clone->start_time);
    }

    public function testClonedEntityCanHaveChildObjectsCopied()
    {
        $obj = new CloneSubject($this->getConn(), [
            'datetime' => new \DateTime('2015-01-01'),
        ]);

        $obj2 = clone $obj;

        $this->assertNotSame($obj->datetime, $obj2->datetime);

        $obj->datetime->modify('+1 day');
        $this->assertSame('2015-01-02', $obj->datetime->format('Y-m-d'));
        $this->assertSame('2015-01-01', $obj2->datetime->format('Y-m-d'));
    }
}
