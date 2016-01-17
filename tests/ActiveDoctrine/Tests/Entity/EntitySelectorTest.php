<?php

namespace ActiveDoctrine\Tests\Entity;

use ActiveDoctrine\Entity\EntitySelector;
use ActiveDoctrine\Selector\MysqlSelector;
use ActiveDoctrine\Tests\Fixtures\Bookshop\Author;

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
        //these calls need to be stubbed so
        //AbstractSelector::fromConnection() works when fetching
        //related objects. See Entity::select() and
        //Entity::selectOne().
        $driver = $this->getMock('Doctrine\DBAL\Driver');
        $driver->expects($this->any())
               ->method('getName')
               ->will($this->returnValue('pdo_mysql'));
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->conn->expects($this->any())
                   ->method('getDriver')
                   ->will($this->returnValue($driver));
        $selector = new MysqlSelector($this->conn, 'books');
        $entity_class = 'ActiveDoctrine\Tests\Fixtures\Bookshop\Book';
        $this->selector = new EntitySelector($selector, $entity_class);
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
        $book = $collection[0];
        $this->assertSame('something', $book->getRaw('name'));
        $this->assertSame(4, $book->getRaw('authors_id'));
    }

    public function testExecuteNoResult()
    {
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                          ->disableOriginalConstructor()
                          ->getMock();
        $statement->expects($this->once())
                  ->method('execute')
                  ->with([4]);
        $statement->expects($this->once())
                  ->method('fetch')
                  ->will($this->returnValue(false));

        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with('SELECT * FROM `books` WHERE `authors_id` = ?')
                   ->will($this->returnValue($statement));

        $collection = $this->selector->where('authors_id', '=', 4)->execute();

        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $collection);
        $this->assertSame(0, count($collection));
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
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
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
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('something', $book->name);

        $details = $book->getRelation('details');
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails', $details);
        $this->assertSame('foo', $details->synopsis);
    }

    public function testExecuteOneWithBelongsTo()
    {
        $details_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $details_statement->expects($this->once())
                          ->method('execute')
                          ->with([]);
        $details_statement->expects($this->once())
                          ->method('fetch')
                          ->will($this->returnValue(['synopsis' => 'foo synopsis', 'books_id' => 4]));

        $book_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                               ->disableOriginalConstructor()
                               ->getMock();
        $book_statement->expects($this->once())
                       ->method('execute')
                       ->with([4]);
        $book_statement->expects($this->once())
                       ->method('fetch')
                       ->will($this->returnValue(['id' => 4, 'name' => 'foo']));

        $this->conn->expects($this->exactly(2))
                   ->method('prepare')
                   ->with($this->logicalOr(
                       'SELECT * FROM `book_details` LIMIT 1',
                       'SELECT * FROM `books` WHERE `id` = ? LIMIT 1'
                   ))
                   ->will($this->onConsecutiveCalls($details_statement, $book_statement));

        $entity_class = 'ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails';
        $selector = new EntitySelector(new MysqlSelector($this->conn, 'book_details'), $entity_class);
        $details = $selector->one()
                            ->with('book')
                            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails', $details);
        $this->assertSame('foo synopsis', $details->synopsis);

        $book = $details->getRelation('book');
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('foo', $book->name);
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

        $entity_class = 'ActiveDoctrine\Tests\Fixtures\Bookshop\Author';
        $selector = new EntitySelector(new MysqlSelector($this->conn, 'authors'), $entity_class);
        $author = $selector->one()
                           ->with('books')
                           ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Author', $author);
        $this->assertSame('author', $author->name);

        $books = $author->getRelation('books');
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(2, count($books));
        $this->assertSame(['foo', 'bar'], $books->getColumn('name'));
    }

    public function testExecuteManyWithHasOne()
    {
        $book_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                               ->disableOriginalConstructor()
                               ->getMock();
        $book_statement->expects($this->once())
                       ->method('execute')
                       ->with([]);
        $book_statement->expects($this->exactly(3))
                       ->method('fetch')
                       ->will($this->onConsecutiveCalls(
                           ['name' => 'foo', 'id' => 4],
                           ['name' => 'bar', 'id' => 5],
                           false
                       ));

        $details_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                                  ->disableOriginalConstructor()
                                  ->getMock();
        $details_statement->expects($this->once())
                          ->method('execute')
                          ->with([4, 5]);
        $details_statement->expects($this->exactly(3))
                          ->method('fetch')
                          ->will($this->onConsecutiveCalls(
                              ['synopsis' => 'bar_details', 'books_id' => 5],
                              ['synopsis' => 'foo_details', 'books_id' => 4],
                              false
                          ));

        $this->conn->expects($this->exactly(2))
                   ->method('prepare')
                   ->with($this->logicalOr(
                       'SELECT * FROM `books` LIMIT 2',
                       'SELECT * FROM `book_details` WHERE `books_id` IN (?, ?)'
                   ))
                   ->will($this->onConsecutiveCalls($book_statement, $details_statement));

        $books = $this->selector->with('details')
                                ->limit(2)
                                ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(2, count($books));
        $this->assertSame(['foo', 'bar'], $books->getColumn('name'));

        for ($i = 0; $i < 2; $i++) {
            $book = $books[$i];
            $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
            $details = $book->getRelation('details');
            $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails', $details);
            $this->assertSame($book->getRaw('name') . '_details', $details->synopsis);
        }
    }

    public function testExecuteManyWithHasMany()
    {
        $author_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $author_statement->expects($this->once())
                         ->method('execute')
                         ->with([]);
        $author_statement->expects($this->exactly(4))
                         ->method('fetch')
                         ->will($this->onConsecutiveCalls(
                             ['name' => 'author_foo', 'id' => 1],
                             ['name' => 'author_bar', 'id' => 2],
                             ['name' => 'author_baz', 'id' => 3],
                             false
                         ));

        $book_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                               ->disableOriginalConstructor()
                               ->getMock();
        $book_statement->expects($this->once())
                       ->method('execute')
                       ->with([1, 2, 3]);
        $book_statement->expects($this->exactly(7))
                       ->method('fetch')
                       ->will($this->onConsecutiveCalls(
                           ['name' => 'book_1', 'authors_id' => 1],
                           ['name' => 'book_2', 'authors_id' => 2],
                           ['name' => 'book_3', 'authors_id' => 2],
                           ['name' => 'book_4', 'authors_id' => 2],
                           ['name' => 'book_5', 'authors_id' => 1],
                           ['name' => 'book_6', 'authors_id' => 2],
                           false
                       ));

        $this->conn->expects($this->exactly(2))
                   ->method('prepare')
                   ->with($this->logicalOr(
                       'SELECT * FROM `authors` LIMIT 3',
                       'SELECT * FROM `books` WHERE `authors_id` IN (?, ?, ?)'
                   ))
                   ->will($this->onConsecutiveCalls($author_statement, $book_statement));

        $entity_class = 'ActiveDoctrine\Tests\Fixtures\Bookshop\Author';
        $selector = new EntitySelector(new MysqlSelector($this->conn, 'authors'), $entity_class);
        $authors = $selector->limit(3)
                            ->with('books')
                            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $authors);
        $this->assertSame(3, count($authors));
        $this->assertSame(['author_foo', 'author_bar', 'author_baz'], $authors->getColumn('name'));

        //author foo
        $foo = $authors[0];
        $books = $foo->getRelation('books');
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(2, count($books));
        $this->assertSame(['book_1', 'book_5'], $books->getColumn('name'));

        //author bar
        $bar = $authors[1];
        $books = $bar->getRelation('books');
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(4, count($books));
        $this->assertSame(['book_2', 'book_3', 'book_4', 'book_6'], $books->getColumn('name'));

        //author baz has no books
        $baz = $authors[2];
        $books = $baz->getRelation('books');
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(0, count($books));
    }

    public function testExecuteManyWithBelongsTo()
    {
        $book_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                               ->disableOriginalConstructor()
                               ->getMock();
        $book_statement->expects($this->once())
                       ->method('execute')
                       ->with([]);
        $book_statement->expects($this->exactly(3))
                       ->method('fetch')
                       ->will($this->onConsecutiveCalls(
                           ['name' => 'foo', 'authors_id' => 4],
                           ['name' => 'bar', 'authors_id' => 5],
                           false
                       ));

        $author_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $author_statement->expects($this->once())
                         ->method('execute')
                         ->with([4, 5]);
        $author_statement->expects($this->exactly(3))
                         ->method('fetch')
                         ->will($this->onConsecutiveCalls(
                             ['name' => 'bar_author', 'id' => 5],
                             ['name' => 'foo_author', 'id' => 4],
                             false
                         ));

        $this->conn->expects($this->exactly(2))
                   ->method('prepare')
                   ->with($this->logicalOr(
                       'SELECT * FROM `books` LIMIT 2',
                       'SELECT * FROM `authors` WHERE `id` IN (?, ?)'
                   ))
                   ->will($this->onConsecutiveCalls($book_statement, $author_statement));

        $books = $this->selector->with('author')
                                ->limit(2)
                                ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(2, count($books));
        $this->assertSame(['foo', 'bar'], $books->getColumn('name'));

        for ($i = 0; $i < 2; $i++) {
            $book = $books[$i];
            $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
            $author = $book->getRelation('author');
            $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Author', $author);
            $this->assertSame($book->getRaw('name') . '_author', $author->name);
        }
    }

    public function testExecuteManyWithNoResult()
    {
        $book_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
            ->disableOriginalConstructor()
            ->getMock();
        $book_statement->expects($this->once())
            ->method('execute')
            ->with([]);
        $book_statement->expects($this->exactly(3))
            ->method('fetch')
            ->will($this->onConsecutiveCalls(
                ['name' => 'foo', 'authors_id' => 4],
                ['name' => 'bar', 'authors_id' => 5],
                false
            ));

        $author_statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
            ->disableOriginalConstructor()
            ->getMock();
        $author_statement->expects($this->once())
            ->method('execute')
            ->with([4, 5]);
        $author_statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(null));

        $this->conn->expects($this->exactly(2))
            ->method('prepare')
            ->with($this->logicalOr(
                'SELECT * FROM `books` LIMIT 2',
                'SELECT * FROM `authors` WHERE `id` IN (?, ?)'
            ))
            ->will($this->onConsecutiveCalls($book_statement, $author_statement));

        $books = $this->selector->with('author')
            ->limit(2)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(2, count($books));
        $this->assertSame(['foo', 'bar'], $books->getColumn('name'));

        for ($i = 0; $i < 2; $i++) {
            $book = $books[$i];
            $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
            $this->assertFalse($book->getRelation('author'));;
        }
    }

    public function testExecuteCount()
    {
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
            ->disableOriginalConstructor()
            ->getMock();
        $statement->expects($this->once())
            ->method('execute')
            ->with([4]);
        $statement->expects($this->once())
            ->method('fetchColumn')
            ->will($this->returnValue("140"));

        $sql = 'SELECT COUNT(1) FROM (SELECT * FROM `books` WHERE `authors_id` = ? LIMIT 200) t';
        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with($sql)
                   ->will($this->returnValue($statement));

        $count = $this->selector->where('authors_id', '=', 4)
            ->limit(200)
            ->count()
            ->execute();

        $this->assertSame(140, $count);
    }

    public function testWhereWithHasOneRelation()
    {
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
            ->disableOriginalConstructor()
            ->getMock();
        $statement->expects($this->once())
            ->method('execute')
            ->with([1]);
        $sql = 'SELECT * FROM `books` WHERE `authors_id` = ? LIMIT 10';
        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with($sql)
                   ->will($this->returnValue($statement));
        $author = new Author($this->conn, ['id' => 1]);

        $books = $this->selector->where('author', $author)
               ->limit(10)
               ->execute();
    }
}
