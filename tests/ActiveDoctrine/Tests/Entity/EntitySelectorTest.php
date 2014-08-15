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
        $driver->expects($this->atLeastOnce())
               ->method('getName')
               ->will($this->returnValue('pdo_mysql'));
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->conn->expects($this->atLeastOnce())
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

    public function testExecuteOne()
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
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Book', $book);
        $this->assertSame('something', $book->getRaw('name'));
        $this->assertSame(4, $book->getRaw('authors_id'));
    }

    public function testExecuteOneWithHasOne()
    {
        $book_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                               ->disableOriginalConstructor()
                               ->getMock();
        $book_statement->expects($this->once())
                       ->method('execute')
                       ->with([]);
        $result = ['name' => 'something', 'id' => 1];
        $book_statement->expects($this->once())
                       ->method('fetch')
                       ->will($this->returnValue($result));

        $details_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $details_statement->expects($this->once())
                          ->method('execute')
                          ->with([1]);
        $result = ['synopsis' => 'foo'];
        $details_statement->expects($this->once())
                          ->method('fetch')
                          ->will($this->returnValue($result));

        $this->conn->expects($this->exactly(2))
                   ->method('prepare')
                   ->with($this->logicalOr(
                       'SELECT * FROM `books` LIMIT 1',
                       'SELECT * FROM `book_details` WHERE `books_id` = ? LIMIT 1'
                   ))
                   ->will($this->onConsecutiveCalls($book_statement, $details_statement));

        $book = $this->selector->one()
                               ->with('details')
                               ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Book', $book);
        $this->assertSame('something', $book->name);

        $details = $book->getRelation('details');
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\BookDetails', $details);
        $this->assertSame('foo', $details->synopsis);
    }

    public function testExecuteOneWithHasMany()
    {
        $author_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $author_statement->expects($this->once())
                         ->method('execute')
                         ->with([]);
        $result = ['name' => 'author', 'id' => 1];
        $author_statement->expects($this->once())
                         ->method('fetch')
                         ->will($this->returnValue($result));

        $book_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                               ->disableOriginalConstructor()
                               ->getMock();
        $book_statement->expects($this->once())
                       ->method('execute')
                       ->with([1]);
        $book_statement->expects($this->exactly(3))
                       ->method('fetch')
                       ->will($this->onConsecutiveCalls(
                           ['name' => 'foo'],
                           ['name' => 'bar'],
                           false
                       ));

        $this->conn->expects($this->exactly(2))
                   ->method('prepare')
                   ->with($this->logicalOr(
                       'SELECT * FROM `authors` LIMIT 1',
                       'SELECT * FROM `books` WHERE `authors_id` = ?'
                   ))
                   ->will($this->onConsecutiveCalls($author_statement, $book_statement));

        $entity_class = 'ActiveDoctrine\Tests\Entity\Author';
        $selector = new EntitySelector($this->conn, $entity_class, 'authors');
        $author = $selector->one()
                           ->with('books')
                           ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Author', $author);
        $this->assertSame('author', $author->name);

        $books = $author->getRelation('books');
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(2, count($books));
        $this->assertSame(['foo', 'bar'], $books->getColumn('name'));
    }

}
