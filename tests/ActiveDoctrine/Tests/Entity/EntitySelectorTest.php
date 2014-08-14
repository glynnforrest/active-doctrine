<?php

namespace ActiveDoctrine\Tests\Entity;

use ActiveDoctrine\Entity\EntitySelector;

/**
 * EntitySelectorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EntitySelectorTest extends \PHPUnit_Framework_TestCase
{

    protected $selector;
    protected $conn;

    public function setUp()
    {
        $driver = $this->getMock('Doctrine\DBAL\Driver');
        $driver->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('pdo_mysql'));
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();
        $this->conn->expects($this->once())
             ->method('getDriver')
             ->will($this->returnValue($driver));

        $entity_class = 'ActiveDoctrine\Tests\Entity\Book';
        $this->selector = new EntitySelector($this->conn, $entity_class, 'books');
    }

    public function test__call()
    {
        //all of the following methods are called in the underlying
        //selector with __call. Mocking the selector is difficult as
        //it is made by EntitySelector so we'll just assert that the
        //resulting sql is what we want. We're using the mysql
        //selector - see setUp().
        $expected = 'SELECT * FROM `books`';
        $this->assertSame($expected, $this->selector->getSQL());

        $this->assertSame($this->selector, $this->selector->where('authors_id', '=', 4));
        $expected = 'SELECT * FROM `books` WHERE `authors_id` = ?';
        $this->assertSame($expected, $this->selector->getSQL());

        $this->assertSame($this->selector, $this->selector->limit(10));
        $expected = 'SELECT * FROM `books` WHERE `authors_id` = ? LIMIT 10';
        $this->assertSame($expected, $this->selector->getSQL());
    }

    public function testExecute()
    {
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                          ->disableOriginalConstructor()
                          ->getMock();
        $statement->expects($this->once())
                  ->method('execute')
                  ->with([4]);
        $result = ['name' => 'something', 'authors_id' => 4];
        $statement->expects($this->exactly(2))
                  ->method('fetch')
                  ->with()
                  ->will($this->onConsecutiveCalls($result, false));

        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with('SELECT * FROM `books` WHERE `authors_id` = ?')
                   ->will($this->returnValue($statement));

        $collection = $this->selector->where('authors_id', '=', 4)->execute();

        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $collection);
        $this->assertSame(1, count($collection));
        $collection->rewind();
        $book = $collection->current();
        $this->assertSame('something', $book->getRaw('name'));
        $this->assertSame(4, $book->getRaw('authors_id'));
    }

    public function testExecuteWithOne()
    {
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                          ->disableOriginalConstructor()
                          ->getMock();
        $statement->expects($this->once())
                  ->method('execute')
                  ->with([4]);
        $result = ['name' => 'something', 'authors_id' => 4];
        $statement->expects($this->once())
                  ->method('fetch')
                  ->will($this->returnValue($result));

        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with('SELECT * FROM `books` WHERE `authors_id` = ? LIMIT 1')
                   ->will($this->returnValue($statement));

        $this->assertSame($this->selector, $this->selector->one());
        $book = $this->selector->where('authors_id', '=', 4)->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\Entity', $book);
        $this->assertSame('something', $book->getRaw('name'));
        $this->assertSame(4, $book->getRaw('authors_id'));
    }

}
