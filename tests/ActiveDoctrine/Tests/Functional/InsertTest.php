<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Tests\Fixtures\MusicFestival\Performance;

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
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $selected);
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
        $this->loadSchema('music_festival');
        $perf = new Performance($this->getConn());
        $perf->name = 'Concert';
        $perf->start_time = new \DateTime();
        $perf->$insert_method();
    }

    /**
     * @dataProvider insertMethodProvider()
     */
    public function testInsertFromConstructor($insert_method)
    {
        $conn = $this->getConn();
        for ($i = 1; $i < 4; $i++) {
            $book = new Book($conn, ['name' => 'foo', 'description' => 'bar', 'authors_id' => 0]);
            $book->$insert_method();
            $this->assertEquals($i, $book->id);
        }
    }

    public function testInsertCallsInsertEvent()
    {
        Book::addEventCallBack('insert', function($book) {
            $book->description = 'description-'.$book->description;
        });

        $conn = $this->getConn();
        for ($i = 1; $i < 4; $i++) {
            $book = new Book($conn, ['name' => 'foo', 'description' => $i, 'authors_id' => 0]);
            $book->save();
        }

        $books = Book::select($conn)->execute();
        $expected = ['description-1', 'description-2', 'description-3'];
        $this->assertSame($expected, $books->getColumn('description'));
        Book::deleteAll($conn);
    }
}
