<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book;

/**
 * UpdateTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class UpdateTest extends FunctionalTestCase
{
    public function testSimpleUpdate()
    {
        $this->loadSchema('bookshop');
        $this->loadData('bookshop');
        $book = Book::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Book 2', $book->name);
        $book->name = 'Hello world';
        $book->update();
        //select it again to check the row has been updated
        $selected = Book::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Hello world', $selected->name);
    }

    public function testSimpleUpdateCallSave()
    {
        $this->loadSchema('bookshop');
        $this->loadData('bookshop');
        $book = Book::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Book 2', $book->name);
        $book->name = 'Hello world';
        $book->save();
        //select it again to check the row has been updated
        $selected = Book::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Hello world', $selected->name);
    }
}
