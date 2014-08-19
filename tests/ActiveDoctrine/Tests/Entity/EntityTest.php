<?php

namespace ActiveDoctrine\Tests\Entity;

use ActiveDoctrine\Entity\EntityCollection;

/**
 * EntityTest
 * @author Glynn Forrest me@glynnforrest.com
 **/
class EntityTest extends \PHPUnit_Framework_TestCase
{

    protected $conn;

    public function setUp()
    {
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                           ->disableOriginalConstructor()
                           ->getMock();
    }

    public function testGetAndSetRaw()
    {
        $obj = new Book($this->conn);

        $obj->setRaw('name', 'test');
        $this->assertSame('test', $obj->getRaw('name'));

        $obj->setRaw('description', 'test');
        $this->assertSame('test', $obj->getRaw('description'));
    }

    public function testGetAndSet()
    {
        $obj = new Book($this->conn);

        //book has a set method that uppercases name
        $obj->set('name', 'test');
        $this->assertSame('TEST', $obj->getRaw('name'));
        $this->assertSame('TEST', $obj->get('name'));

        //book has a get method that uppercases description
        $obj->set('description', 'test');
        $this->assertSame('test', $obj->getRaw('description'));
        $this->assertSame('TEST', $obj->get('description'));
    }

    public function testMagicGetAndSet()
    {
        $obj = new Book($this->conn);

        $obj->name = 'test';
        $this->assertSame('TEST', $obj->name);

        $obj->description = 'test';
        $this->assertSame('TEST', $obj->description);
    }

    public function testCreateWithValues()
    {
        $obj = new Book($this->conn, ['name' => 'foo', 'description' => 'bar']);

        $this->assertSame('foo', $obj->getRaw('name'));
        $this->assertSame('bar', $obj->getRaw('description'));
    }

    public function testGetModifiedFields()
    {
        $obj = new Book($this->conn, ['name' => 'foo', 'description' => 'bar']);
        $this->assertSame([], $obj->getModifiedFields());

        $obj->setRaw('name', 'foo');
        $this->assertSame([], $obj->getModifiedFields());

        $obj->setRaw('name', 'changed');
        $this->assertSame(['name'], $obj->getModifiedFields());

        $obj->setRaw('description', 'changed');
        $this->assertSame(['name', 'description'], $obj->getModifiedFields());
    }

    public function testGetAndSetValues()
    {
        $obj = new Book($this->conn);
        $obj->setValues(['name' => 'foo', 'description' => 'bar']);

        //set methods should have been called in setValues
        $this->assertSame('FOO', $obj->getRaw('name'));
        $this->assertSame('bar', $obj->getRaw('description'));

        //get methods should be called in getValues
        $expected = ['name' => 'FOO', 'description' => 'BAR'];
        $this->assertSame($expected, $obj->getValues());
    }

    public function testGetAndSetValuesRaw()
    {
        $obj = new Book($this->conn);

        $obj->setValuesRaw(['name' => 'foo', 'description' => 'bar']);
        //set methods should not have been called in setValuesRaw
        $this->assertSame('foo', $obj->getRaw('name'));
        $this->assertSame('bar', $obj->getRaw('description'));

        //get methods should not be called in getValuesRaw
        $expected = ['name' => 'foo', 'description' => 'bar'];
        $this->assertSame($expected, $obj->getValuesRaw());
    }

    public function testSetAndIsStored()
    {
        $obj = new Book($this->conn);
        $this->assertFalse($obj->isStored());

        $this->assertSame($obj, $obj->setStored());
        $this->assertTrue($obj->isStored());

        $this->assertSame($obj, $obj->setStored(false));
        $this->assertFalse($obj->isStored());

        $this->assertSame($obj, $obj->setStored(true));
        $this->assertTrue($obj->isStored());
    }

    public function testInsert()
    {
        $obj = new Book($this->conn);

        $obj->name = 'foo';
        $obj->description = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO', 'description' => 'bar']);

        $this->assertFalse($obj->isStored());
        $obj->insert();
        $this->assertTrue($obj->isStored());
    }

    public function testInsertNotModified()
    {
        $obj = new Book($this->conn);
        $this->conn->expects($this->never())
                   ->method('insert');

        $obj->insert();

    }

    public function testInsertUnknownFields()
    {
        $obj = new Book($this->conn);

        $obj->name = 'foo';
        $obj->something = 'bar';
        $this->assertSame('bar', $obj->something);

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO']);

        $obj->insert();
    }

    public function testInsertUnknownFieldsOnly()
    {
        $obj = new Book($this->conn);

        $obj->something = 'bar';
        $this->assertSame('bar', $obj->something);

        $this->conn->expects($this->never())
                   ->method('insert');

        $obj->insert();
    }

    public function testInsertThrowsExceptionWhenAlreadySaved()
    {
        $obj = new Book($this->conn);

        $obj->name = 'foo';
        $obj->description = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO', 'description' => 'bar']);

        $obj->insert();

        $this->setExpectedException('\LogicException');
        $obj->insert();
    }

    public function testInsertThrowsExceptionAfterModification()
    {
        $obj = new Book($this->conn);

        $obj->name = 'foo';
        $obj->description = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO', 'description' => 'bar']);

        $obj->insert();

        $obj->name = 'foo2';
        $this->setExpectedException('\LogicException');
        $obj->insert();
    }

    public function testUpdate()
    {
        $obj = new Book($this->conn);

        $obj->id = 1;
        $obj->name = 'foo';
        $obj->description = 'bar';

        $this->conn->expects($this->once())
                   ->method('update')
                   ->with(
                       'books',
                       ['id' => 1, 'name' => 'FOO', 'description' => 'bar'],
                       ['id' => 1]
                   );

        $this->assertFalse($obj->isStored());
        $obj->update();
        $this->assertTrue($obj->isStored());
    }

    public function testUpdateAfterInsert()
    {
        $obj = new Book($this->conn);

        $obj->name = 'foo';
        $obj->description = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO', 'description' => 'bar']);

        $this->conn->expects($this->once())
                   ->method('lastInsertId')
                   ->will($this->returnValue(42));

        $obj->insert();

        $obj->description = 'baz';

        $this->conn->expects($this->once())
                   ->method('update')
                   ->with(
                       'books',
                       ['description' => 'baz'],
                       ['id' => 42]
                   );

        $obj->update();
    }

    public function testUpdateNoChangedFields()
    {
        $obj = new Book($this->conn, ['id' => 1, 'name' => 'foo']);

        $this->conn->expects($this->never())
                   ->method('update');

        $obj->update();
    }

    public function testUpdateAfterUpdateNoChangedFields()
    {
        $obj = new Book($this->conn);
        $obj->id = 1;
        $obj->name = 'foo';
        $obj->description = 'bar';

        $this->conn->expects($this->once())
                   ->method('update')
                   ->with(
                       'books',
                       ['id' => 1, 'name' => 'FOO', 'description' => 'bar'],
                       ['id' => 1]
                   );

        $obj->update();
        $obj->update();
    }

    public function testUpdateThrowsExceptionWithNoPrimaryKey()
    {
        $obj = new Book($this->conn);
        $obj->name = 'foo';
        $this->setExpectedException('\LogicException');
        $obj->update();
    }

    public function testUpdateWithPrimaryKeyChange()
    {
        $obj = new Book($this->conn, ['name' => 'bar', 'id' => 1]);
        $obj->name = 'foo';
        $obj->id = 3;

        $this->conn->expects($this->once())
                   ->method('update')
                   ->with(
                       'books',
                       ['id' => 3, 'name' => 'FOO'],
                       //the id of 1 came from a result set so is
                       //considered to be the id in the database
                       ['id' => 1]
                   );
        $obj->update();
    }

    public function testUpdateWithManyPrimaryKeyChanges()
    {
        $obj = new Book($this->conn, ['name' => 'bar', 'id' => 1]);
        $obj->name = 'foo';
        $obj->id = 3;
        $obj->id = 4;
        $obj->id = 5;

        $this->conn->expects($this->once())
                   ->method('update')
                   ->with(
                       'books',
                       ['id' => 5, 'name' => 'FOO'],
                       //the id of 1 came from a result set so is
                       //considered to be the id in the database
                       ['id' => 1]
                   );
        $obj->update();
    }

    public function testUpdateWithPrimaryKeyChangeNotFromResultSet()
    {
        $obj = new Book($this->conn);
        $obj->name = 'foo';
        $obj->id = 3;
        $obj->id = 4;
        $obj->id = 5;

        $this->conn->expects($this->once())
                   ->method('update')
                   ->with(
                       'books',
                       ['id' => 5, 'name' => 'FOO'],
                       //since the id didn't come from a result set, 5 is considered
                       //to be the id in the database
                       ['id' => 5]
                   );
        $obj->update();
    }

    public function testDelete()
    {
        $obj = new Book($this->conn, ['id' => 1, 'name' => 'bar']);
        $this->conn->expects($this->once())
                   ->method('delete')
                   ->with(
                       'books',
                       ['id' => 1]
                   );
        $obj->delete();
    }

    public function testDeleteWithNoPrimaryKey()
    {
        $obj = new Book($this->conn);
        $this->setExpectedException('\LogicException');
        $obj->delete();
    }

    public function testDeleteWithUpdatedPrimaryKey()
    {
        $obj = new Book($this->conn, ['id' => 1, 'name' => 'bar']);
        $obj->id = 4;

        $this->conn->expects($this->once())
                   ->method('delete')
                   ->with(
                       'books',
                       ['id' => 1]
                   );
        $obj->delete();
    }

    public function testSaveInsert()
    {
        $obj = $this->getMockBuilder('ActiveDoctrine\Tests\Entity\Book')
                    ->disableOriginalConstructor()
                    ->setMethods(['insert'])
                    ->getMock();

        $obj->name = 'bar';

        $obj->expects($this->once())
            ->method('insert');

        $obj->save();
    }

    public function testSaveUpdate()
    {
        $obj = $this->getMockBuilder('ActiveDoctrine\Tests\Entity\Book')
                    ->disableOriginalConstructor()
                    ->setMethods(['update'])
                    ->getMock();

        $obj->id = 2;
        $obj->name = 'bar';
        $obj->setStored();

        $obj->expects($this->once())
            ->method('update');

        $obj->save();
    }

    public function testDeleteAll()
    {
        $this->conn->expects($this->once())
                   ->method('delete')
                   ->with('books');

        Book::deleteAll($this->conn);
    }

    public function testCollection()
    {
        $collection = Book::collection($this->conn);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $collection);
        $this->assertSame('books', $collection->getTable());
        $this->assertSame('id', $collection->getPrimaryKey());
        $this->assertSame('ActiveDoctrine\Tests\Entity\Book', $collection->getEntityClass());
    }

    public function testCollectionWithEntities()
    {
        $collection = Book::collection($this->conn, 3);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $collection);
        $entities = $collection->getEntities();
        $this->assertSame(3, count($entities));
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Book', $entities[0]);
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Book', $entities[1]);
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Book', $entities[2]);
    }

    public function testSelectSQL()
    {
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                          ->disableOriginalConstructor()
                          ->getMock();
        $sql = 'select * from books';
        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with($sql)
                   ->will($this->returnValue($statement));

        for ($i = 1; $i < 4; $i++) {
            ${'result' . $i} = ['name' => "name$i", 'description' => "description$i"];
        }

        $statement->expects($this->exactly(4))
                  ->method('fetch')
                  ->with()
                  ->will($this->onConsecutiveCalls($result1, $result2, $result3, false));


        $collection = Book::selectSQL($this->conn, $sql);

        $this->assertSame(3, count($collection));
        $collection->rewind();

        for ($i = 1; $i < 4; $i++) {
            $book = $collection->current();
            $this->assertSame("name$i", $book->getRaw('name'));
            $this->assertSame("description$i", $book->getRaw('description'));
            $this->assertTrue($book->isStored());
            $collection->next();
        }
    }

    public function testSelectOneSQL()
    {
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                          ->disableOriginalConstructor()
                          ->getMock();
        $sql = 'select * from books where id = ?';
        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with($sql)
                   ->will($this->returnValue($statement));

        $result = ['name' => 'foo', 'description' => 'bar'];

        $statement->expects($this->once())
                  ->method('execute')
                  ->with([1]);

        $statement->expects($this->once())
                  ->method('fetch')
                  ->will($this->returnValue($result));

        $book = Book::selectOneSQL($this->conn, $sql, [1]);

        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Book', $book);
        $this->assertSame('foo', $book->getRaw('name'));
        $this->assertSame('bar', $book->getRaw('description'));
        $this->assertTrue($book->isStored());
    }

    public function testSelectOneSQLReturnsNull()
    {
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                          ->disableOriginalConstructor()
                          ->getMock();
        $sql = 'select * from books where id = ?';
        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with($sql)
                   ->will($this->returnValue($statement));

        $statement->expects($this->once())
                  ->method('fetch')
                  ->will($this->returnValue(false));

        $this->assertNull(Book::selectOneSQL($this->conn, $sql, [1]));
    }

    protected function expectDriver()
    {
        $driver = $this->getMock('Doctrine\DBAL\Driver');
        $driver->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('pdo_mysql'));
        $this->conn->expects($this->once())
             ->method('getDriver')
             ->will($this->returnValue($driver));
    }

    public function testSelect()
    {
        $this->expectDriver();
        $selector = Book::select($this->conn);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntitySelector', $selector);
        $this->assertSame('SELECT * FROM `books`', $selector->getSQL());
    }

    public function testSelectOne()
    {
        $this->expectDriver();
        $selector = Book::selectOne($this->conn);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntitySelector', $selector);
        $this->assertSame('SELECT * FROM `books` LIMIT 1', $selector->getSQL());
    }

    public function testGetRelationHasOne()
    {
        $book = new Book($this->conn, ['id' => 5]);
        $driver = $this->expectDriver();
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->conn->expects($this->once())
               ->method('prepare')
               ->with('SELECT * FROM `book_details` WHERE `books_id` = ? LIMIT 1')
               ->will($this->returnValue($statement));
        $statement->expects($this->once())
                  ->method('execute')
                  ->with([5]);
        $result = ['synopsis' => 'foo'];
        $statement->expects($this->once())
                  ->method('fetch')
                  ->will($this->returnValue($result));

        $details = $book->getRelation('details');
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\BookDetails', $details);
        $this->assertSame('foo', $details->synopsis);
        /* the query should not be executed more than once */
        $book->getRelation('details');
    }

    public function testSetRelation()
    {
        $book = new Book($this->conn);
        $details = new BookDetails($this->conn);
        $book->setRelation('details', $details);
        $this->assertSame($details, $book->getRelation('details'));
    }

    public function testGetRelationDefintion()
    {
        $relation = Book::getRelationDefinition('details');
        $this->assertSame(['has_one', 'ActiveDoctrine\\Tests\\Entity\\BookDetails', 'books_id', 'id'], $relation);
    }

    public function testGetRelationDefintionThrowsExceptionOnUnknownRelation()
    {
        $msg = 'Relation "unknown" of Entity "ActiveDoctrine\Tests\Entity\Book" is not defined';
        $this->setExpectedException('\Exception', $msg);
        Book::getRelationDefinition('unknown');
    }

    public function testGetRelationDefintionThrowsExceptionOnInvalidRelation()
    {
        $msg = 'Relation "invalid" of Entity "ActiveDoctrine\Tests\Entity\Book" is invalid';
        $this->setExpectedException('\Exception', $msg);
        Book::getRelationDefinition('invalid');
    }

    public function testGetRelationHasMany()
    {
        $author = new Author($this->conn, ['id' => 2]);
        $driver = $this->expectDriver();
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->conn->expects($this->once())
               ->method('prepare')
               ->with('SELECT * FROM `books` WHERE `authors_id` = ?')
               ->will($this->returnValue($statement));
        $statement->expects($this->once())
                  ->method('execute')
                  ->with([2]);
        $result1 = ['name' => 'foo'];
        $result2 = ['name' => 'bar'];
        $statement->expects($this->exactly(3))
                  ->method('fetch')
                  ->will($this->onConsecutiveCalls($result1, $result2, false));

        $books = $author->getRelation('books');
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(2, count($books));

        $first = $books[0];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Book', $first);
        $this->assertSame('foo', $first->name);

        $second = $books[1];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Book', $second);
        $this->assertSame('bar', $second->name);

        /* the query should not be executed more than once */
        $author->getRelation('books');
    }

    public function testGetRawCallsGetRelation()
    {
        $book = new Book($this->conn);
        $details = new BookDetails($this->conn);
        $book->setRelation('details', $details);
        $this->assertSame($details, $book->getRaw('details'));
        $this->assertSame($details, $book->get('details'));
        $this->assertSame($details, $book->getRelation('details'));
        $this->assertSame($details, $book->details);
    }

    public function testSetRawCallSetRelation()
    {
        $book = new Book($this->conn);
        $details = new BookDetails($this->conn);
        $book->setRaw('details', $details);
        $this->assertSame($details, $book->getRelation('details'));
    }

    public function testHas()
    {
        $book = new Book($this->conn);

        $this->assertFalse($book->has('name'));
        $book->setRaw('name', 'foo');
        $this->assertTrue($book->has('name'));

        //not a column
        $this->assertFalse($book->has('something'));
        $book->setRaw('something', 'foo');
        $this->assertTrue($book->has('something'));

        //related object
        $details = new BookDetails($this->conn);
        $book->setRelation('details', $details);
        $this->assertTrue($book->has('details'));

        //related object collection, but empty
        $books = new EntityCollection($this->conn);
        $author = new Author($this->conn);
        $author->setRelation('books', $books);
        $this->assertFalse($author->has('books'));

        //object collection containing entities
        $books[] = $book;
        $this->assertTrue($author->has('books'));
    }

}
