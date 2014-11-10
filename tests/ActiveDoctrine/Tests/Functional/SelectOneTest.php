<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book;

/**
 * SelectOneTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelectOneTest extends FunctionalTestCase
{

    public function setup()
    {
        $this->loadSchema('bookshop');
    }

    public function testEmptyTable()
    {
        $this->assertNull(Book::selectOne($this->getConn())->execute());
    }

    public function testSingleId()
    {
        $this->loadData('bookshop');
        $book = Book::selectOne($this->getConn())
            ->where('id', '=', 3)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book', $book);
        $this->assertSame('3', $book->id);
    }

    public function testOneFromManyResults()
    {
        $this->loadData('bookshop');
        $book = Book::selectOne($this->getConn())
            ->orderBy('id', 'DESC')
            ->limit(100)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book', $book);
        $this->assertSame('50', $book->id);
    }

}
