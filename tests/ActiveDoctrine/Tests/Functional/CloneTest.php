<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;

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
}
