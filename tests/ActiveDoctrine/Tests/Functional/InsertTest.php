<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book;
use ActiveDoctrine\Tests\Fixtures\Entities\Events\Event;

/**
 * InsertTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class InsertTest extends FunctionalTestCase
{
    public function setup()
    {
        $this->loadSchema('bookshop');
    }

    public function insertMethodProvider()
    {
        return [
            ['insert'],
            ['save']
        ];
    }

    /**
     * @dataProvider insertMethodProvider()
     */
    public function testInsertOne($insert_method)
    {
        $conn = $this->getConn();
        $book = new Book($conn);
        $book->name = 'Foo';
        $book->description = 'Bar';
        $book->authors_id = 0;
        $book->$insert_method();
        //select it to check it has been inserted
        $selected = Book::selectOne($conn)->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book', $selected);
        $this->assertEquals($book->getValues(), $selected->getValues());
    }

    /**
     * @dataProvider insertMethodProvider()
     */
    public function testInsertAddsPrimaryKey($insert_method)
    {
        $conn = $this->getConn();
        for ($i = 1; $i < 4; $i++) {
            $book = new Book($conn);
            $book->name = 'Foo';
            $book->description = 'Bar';
            $book->authors_id = 0;
            $book->$insert_method();
            $this->assertEquals($i, $book->id);
        }
    }

    /**
     * @dataProvider insertMethodProvider()
     */
    public function testInsertTypeDatetime($insert_method)
    {
        $this->loadSchema('events');
        $event = new Event($this->getConn());
        $event->name = 'Concert';
        $event->start_time = new \DateTime();
        $event->$insert_method();
    }
}
