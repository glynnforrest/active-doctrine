<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Tests\Fixtures\Events\Event;

/**
 * UpdateTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class UpdateTest extends FunctionalTestCase
{
    public function updateMethodProvider()
    {
        return [
            ['update'],
            ['save']
        ];
    }

    /**
     * @dataProvider updateMethodProvider()
     */
    public function testSimpleUpdate($update_method)
    {
        $this->loadSchema('bookshop');
        $this->loadData('bookshop');
        $book = Book::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Book 2', $book->name);
        $book->name = 'Hello world';
        $book->$update_method();
        //select it again to check the row has been updated
        $selected = Book::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Hello world', $selected->name);
    }

    /**
     * @dataProvider updateMethodProvider()
     */
    public function testUpdateWithType($update_method)
    {
        $this->loadSchema('events');
        $this->loadData('events');
        $event = Event::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Millennium', $event->name);
        $event->name = 'Party';
        $now = new \DateTime();
        $event->start_time = $now;
        $event->$update_method();
        //select it again to check the row has been updated
        $selected = Event::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Party', $selected->name);
        $this->assertEquals($now, $selected->start_time);
    }
}
