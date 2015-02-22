<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Tests\Fixtures\MusicFestival\Performance;

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
        $this->loadSchema('music_festival');
        $this->loadData('music_festival');
        $perf = Performance::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Millennium', $perf->name);
        $perf->name = 'Party';
        $now = new \DateTime();
        $perf->start_time = $now;
        $perf->$update_method();
        //select it again to check the row has been updated
        $selected = Performance::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();
        $this->assertSame('Party', $selected->name);
        $this->assertEquals($now, $selected->start_time);
    }

    /**
     * @dataProvider updateMethodProvider
     */
    public function testUpdateCallsUpdateEvent($update_method)
    {
        Book::addEventCallBack('update', function($book) {
            $book->description = 'update-'.$book->description;
        });

        $this->loadSchema('bookshop');
        $this->loadData('bookshop');

        $conn = $this->getConn();
        $book = Book::selectOne($this->getConn())
            ->where('id', '=', 2)
            ->execute();

        $book->$update_method();

        $this->assertSame('update-The second book', $book->description);

        Book::resetEventCallbacks();
    }
}
