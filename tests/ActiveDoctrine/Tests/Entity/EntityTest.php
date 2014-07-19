<?php

namespace ActiveDoctrine\Tests\Entity;

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

        $obj->setRaw('author', 'test');
        $this->assertSame('test', $obj->getRaw('author'));
    }

    public function testGetAndSet()
    {
        $obj = new Book($this->conn);

        //book has a set method that uppercases author
        $obj->set('name', 'test');
        $this->assertSame('TEST', $obj->getRaw('name'));
        $this->assertSame('TEST', $obj->get('name'));

        //book has a get method that uppercases author
        $obj->set('author', 'test');
        $this->assertSame('test', $obj->getRaw('author'));
        $this->assertSame('TEST', $obj->get('author'));
    }

    public function testMagicGetAndSet()
    {
        $obj = new Book($this->conn);

        $obj->name = 'test';
        $this->assertSame('TEST', $obj->name);

        $obj->author = 'test';
        $this->assertSame('TEST', $obj->author);
    }

    public function testCreateWithValues()
    {
        $obj = new Book($this->conn, ['name' => 'foo', 'author' => 'bar']);

        $this->assertSame('foo', $obj->getRaw('name'));
        $this->assertSame('bar', $obj->getRaw('author'));
    }

    public function testGetModifiedFields()
    {
        $obj = new Book($this->conn, ['name' => 'foo', 'author' => 'bar']);
        $this->assertSame([], $obj->getModifiedFields());

        $obj->setRaw('name', 'foo');
        $this->assertSame([], $obj->getModifiedFields());

        $obj->setRaw('name', 'changed');
        $this->assertSame(['name'], $obj->getModifiedFields());

        $obj->setRaw('author', 'changed');
        $this->assertSame(['name', 'author'], $obj->getModifiedFields());
    }

    public function testGetAndSetValues()
    {
        $obj = new Book($this->conn);
        $obj->setValues(['name' => 'foo', 'author' => 'bar']);

        //set methods should have been called in setValues
        $this->assertSame('FOO', $obj->getRaw('name'));
        $this->assertSame('bar', $obj->getRaw('author'));

        //get methods should be called in getValues
        $expected = ['name' => 'FOO', 'author' => 'BAR'];
        $this->assertSame($expected, $obj->getValues());
    }

    public function testGetAndSetValuesRaw()
    {
        $obj = new Book($this->conn);

        $obj->setValuesRaw(['name' => 'foo', 'author' => 'bar']);
        //set methods should not have been called in setValuesRaw
        $this->assertSame('foo', $obj->getRaw('name'));
        $this->assertSame('bar', $obj->getRaw('author'));

        //get methods should not be called in getValuesRaw
        $expected = ['name' => 'foo', 'author' => 'bar'];
        $this->assertSame($expected, $obj->getValuesRaw());
    }

    public function testSetAndIsStored()
    {
        $obj = new Book($this->conn);
        $this->assertFalse($obj->isStored());

        $obj->setStored();
        $this->assertTrue($obj->isStored());

        $obj->setStored(false);
        $this->assertFalse($obj->isStored());

        $obj->setStored(true);
        $this->assertTrue($obj->isStored());
    }

    public function testInsert()
    {
        $obj = new Book($this->conn);

        $obj->name = 'foo';
        $obj->author = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO', 'author' => 'bar']);

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
        $obj->author = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO', 'author' => 'bar']);

        $obj->insert();

        $this->setExpectedException('\LogicException');
        $obj->insert();
    }

    public function testInsertThrowsExceptionAfterModification()
    {
        $obj = new Book($this->conn);

        $obj->name = 'foo';
        $obj->author = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO', 'author' => 'bar']);

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
        $obj->author = 'bar';

        $this->conn->expects($this->once())
                   ->method('update')
                   ->with(
                       'books',
                       ['id' => 1, 'name' => 'FOO', 'author' => 'bar'],
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
        $obj->author = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO', 'author' => 'bar']);

        $this->conn->expects($this->once())
                   ->method('lastInsertId')
                   ->will($this->returnValue(42));

        $obj->insert();

        $obj->author = 'baz';

        $this->conn->expects($this->once())
                   ->method('update')
                   ->with(
                       'books',
                       ['author' => 'baz'],
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
        $obj->author = 'bar';

        $this->conn->expects($this->once())
                   ->method('update')
                   ->with(
                       'books',
                       ['id' => 1, 'name' => 'FOO', 'author' => 'bar'],
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
            ${'result' . $i} = ['name' => "name$i", 'author' => "author$i"];
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
            $this->assertSame("author$i", $book->getRaw('author'));
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

        $result = ['name' => 'foo', 'author' => 'bar'];

        $statement->expects($this->once())
                  ->method('execute')
                  ->with([1]);

        $statement->expects($this->once())
                  ->method('fetch')
                  ->will($this->returnValue($result));

        $book = Book::selectOneSQL($this->conn, $sql, [1]);

        $this->assertInstanceOf('ActiveDoctrine\Tests\Entity\Book', $book);
        $this->assertSame('foo', $book->getRaw('name'));
        $this->assertSame('bar', $book->getRaw('author'));
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

    public function testSelect()
    {
        $driver = $this->getMock('Doctrine\DBAL\Driver');
        $driver->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('pdo_mysql'));
        $this->conn->expects($this->once())
             ->method('getDriver')
             ->will($this->returnValue($driver));

        $selector = Book::select($this->conn);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntitySelector', $selector);
        $this->assertSame('SELECT * FROM `books`', $selector->getSQL());
    }

    public function testSelectOne()
    {
        $driver = $this->getMock('Doctrine\DBAL\Driver');
        $driver->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('pdo_mysql'));
        $this->conn->expects($this->once())
             ->method('getDriver')
             ->will($this->returnValue($driver));

        $selector = Book::selectOne($this->conn);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntitySelector', $selector);
        $this->assertSame('SELECT * FROM `books` LIMIT 1', $selector->getSQL());
    }

}
