<?php

namespace ActiveDoctrine\Tests\Entity;

use ActiveDoctrine\Entity\EntityCollection;
use ActiveDoctrine\Tests\Fixtures\SetterGetter\UpperCase;
use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails;
use ActiveDoctrine\Tests\Fixtures\Bookshop\Author;
use ActiveDoctrine\Tests\Fixtures\Articles\Article;

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

    public function testGetFields()
    {
        $this->assertSame(['id', 'name', 'description', 'authors_id'], Book::getFields());
    }

    public function testGetAndSetRaw()
    {
        $obj = new UpperCase($this->conn);

        $obj->setRaw('name', 'test');
        $this->assertSame('test', $obj->getRaw('name'));

        $obj->setRaw('description', 'test');
        $this->assertSame('test', $obj->getRaw('description'));
    }

    public function testGetAndSet()
    {
        $obj = new UpperCase($this->conn);

        //UpperCase has a set method that uppercases name
        $obj->set('name', 'test');
        $this->assertSame('TEST', $obj->getRaw('name'));
        $this->assertSame('TEST', $obj->get('name'));

        //UpperCase has a get method that uppercases description
        $obj->set('description', 'test');
        $this->assertSame('test', $obj->getRaw('description'));
        $this->assertSame('TEST', $obj->get('description'));
    }

    public function testMagicGetAndSet()
    {
        $obj = new UpperCase($this->conn);

        $obj->name = 'test';
        $this->assertSame('TEST', $obj->name);

        $obj->description = 'test';
        $this->assertSame('TEST', $obj->description);
    }

    public function testCreateWithValues()
    {
        $obj = new UpperCase($this->conn, ['name' => 'foo', 'description' => 'bar']);

        $this->assertSame('foo', $obj->getRaw('name'));
        $this->assertSame('bar', $obj->getRaw('description'));
    }

    public function testGetModifiedFields()
    {
        $obj = new UpperCase($this->conn, ['name' => 'foo', 'description' => 'bar']);
        $this->assertSame(['name', 'description'], $obj->getModifiedFields());

        $obj->setStored();
        $this->assertSame([], $obj->getModifiedFields());

        $obj->setRaw('name', 'foo');
        $this->assertSame([], $obj->getModifiedFields());

        $obj->setRaw('name', 'changed');
        $this->assertSame(['name'], $obj->getModifiedFields());

        $obj->setRaw('description', 'changed');
        $this->assertSame(['name', 'description'], $obj->getModifiedFields());
    }

    public function testIsModified()
    {
        $obj = new UpperCase($this->conn, ['name' => 'foo', 'description' => 'bar']);
        $this->assertTrue($obj->isModified());

        $obj->setStored();
        $this->assertFalse($obj->isModified());

        //setting a column that isn't part of the entity
        $obj->set('foo', 'bar');
        $this->assertFalse($obj->isModified());

        //name is part of the entity
        $obj->set('name', 'bar');
        $this->assertTrue($obj->isModified());
    }

    public function testGetAndSetValues()
    {
        $obj = new UpperCase($this->conn);
        $obj->setValues(['name' => 'foo', 'description' => 'bar']);

        //set methods should have been called in setValues
        $this->assertSame('FOO', $obj->getRaw('name'));
        $this->assertSame('bar', $obj->getRaw('description'));
        $this->assertSame(['name', 'description'], $obj->getModifiedFields());

        //get methods should be called in getValues
        $expected = ['name' => 'FOO', 'description' => 'BAR'];
        $this->assertSame($expected, $obj->getValues());
    }

    public function testGetAndSetValuesRaw()
    {
        $obj = new UpperCase($this->conn);

        $obj->setValuesRaw(['name' => 'foo', 'description' => 'bar']);

        //set methods should not have been called in setValuesRaw
        $this->assertSame('foo', $obj->getRaw('name'));
        $this->assertSame('bar', $obj->getRaw('description'));
        $this->assertSame(['name', 'description'], $obj->getModifiedFields());

        //get methods should not be called in getValuesRaw
        $expected = ['name' => 'foo', 'description' => 'bar'];
        $this->assertSame($expected, $obj->getValuesRaw());
    }

    public function testSetValuesSafe()
    {
        $book = new Book($this->conn);
        $book->setValuesSafe([
            'id' => 34,
            'authors_id' => 100,
            'name' => 'The Art of War',
            'description' => 'Foo',
        ]);
        $this->assertSame([
            'name' => 'The Art of War',
            'description' => 'Foo',
        ], $book->getValues());
    }

    public function testSetValuesSafeIgnoresPrimaryKeyByDefault()
    {
        $author = new Author($this->conn);
        $author->setValuesSafe([
            'id' => 34,
            'name' => 'Thomas Hardy',
        ]);
        $this->assertSame([
            'name' => 'Thomas Hardy',
        ], $author->getValues());
    }

    public function testSetValuesSafeCanSetPrimaryKey()
    {
        //blacklist doesn't contain 'id', so id should be allowed
        $details = new BookDetails($this->conn);
        $details->setValuesSafe([
            'id' => 20,
            'books_id' => 40,
            'synopsis' => 'foo'
        ]);
        $this->assertSame([
            'id' => 20,
            'synopsis' => 'foo'
        ], $details->getValues());
    }

    public function testSetValuesSafeCanAcceptAnything()
    {
        //blacklist is set to [], so should allow anything
        $article = new Article($this->conn);
        $values = [
            'id' => 12,
            'title' => 'Foo',
            'slug' => 'foo',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];
        $article->setValuesSafe($values);
        $this->assertSame($values, $article->getValues());
    }

    public function testSetAndIsStored()
    {
        $obj = new UpperCase($this->conn);
        $this->assertFalse($obj->isStored());

        $this->assertSame($obj, $obj->setStored());
        $this->assertTrue($obj->isStored());

        $this->assertSame($obj, $obj->setStored(false));
        $this->assertFalse($obj->isStored());

        $this->assertSame($obj, $obj->setStored(true));
        $this->assertTrue($obj->isStored());
    }

    public function testSetStoredRestoresModifiedFields()
    {
        $obj = new UpperCase($this->conn, ['name' => 'foo']);
        $this->assertSame(['name'], $obj->getModifiedFields());

        $obj->setStored();
        $this->assertSame([], $obj->getModifiedFields());

        $obj->setStored(false);
        $this->assertSame(['name'], $obj->getModifiedFields());
    }

    public function testInsert()
    {
        $obj = new Book($this->conn);

        $obj->name = 'foo';
        $obj->description = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'foo', 'description' => 'bar']);

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
                   ->with('books', ['name' => 'foo']);

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

    public function testInsertUnknownFieldsFromConstructor()
    {
        $obj = new Book($this->conn, ['something' => 'bar']);

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
                   ->with('books', ['name' => 'foo', 'description' => 'bar']);

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
                   ->with('books', ['name' => 'foo', 'description' => 'bar']);

        $obj->insert();

        $obj->name = 'foo2';
        $this->setExpectedException('\LogicException');
        $obj->insert();
    }

    public function testInsertFromConstructor()
    {
        $obj = new Book($this->conn, ['name' => 'foo', 'description' => 'bar']);
        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'foo', 'description' => 'bar']);

        $obj->insert();
    }

    public function testInsertFromConstructorAndModification()
    {
        $obj = new Book($this->conn, ['name' => 'foo']);
        $obj->description = 'bar';
        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'foo', 'description' => 'bar']);

        $obj->insert();
    }

    public function testInsertAfterSetStored()
    {
        $book = new Book($this->conn, ['name' => 'foo', 'description' => 'bar']);

        //the book is stored, don't save it
        $book->setStored();

        //oh wait, no it isn't
        $book->setStored(false);

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'foo', 'description' => 'bar']);

        $book->insert();
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
                       ['id' => 1, 'name' => 'foo', 'description' => 'bar'],
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
                   ->with('books', ['name' => 'foo', 'description' => 'bar']);

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
        $obj->setStored();

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
                       ['id' => 1, 'name' => 'foo', 'description' => 'bar'],
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
                       ['id' => 3, 'name' => 'foo'],
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
                       ['id' => 5, 'name' => 'foo'],
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
                       ['id' => 5, 'name' => 'foo'],
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
        $this->assertSame($obj, $obj->delete());
    }

    public function testDeleteWithNoPrimaryKey()
    {
        $obj = new Book($this->conn);
        $this->setExpectedException('\LogicException');
        $this->assertSame($obj, $obj->delete());
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
        $this->assertSame($obj, $obj->delete());
    }

    public function testSaveInsert()
    {
        $obj = $this->getMockBuilder('ActiveDoctrine\Tests\Fixtures\Bookshop\Book')
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
        $obj = $this->getMockBuilder('ActiveDoctrine\Tests\Fixtures\Bookshop\Book')
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

    public function testNewCollection()
    {
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', Book::newCollection());
    }

    public function testCreate()
    {
        $collection = Book::create($this->conn, 3);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $collection);
        $entities = $collection->getEntities();
        $this->assertSame(3, count($entities));
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $entities[0]);
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $entities[1]);
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $entities[2]);
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

        for ($i = 1; $i < 4; $i++) {
            $book = $collection[$i - 1];
            $this->assertSame("name$i", $book->getRaw('name'));
            $this->assertSame("description$i", $book->getRaw('description'));
            $this->assertTrue($book->isStored());
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

        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
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
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails', $details);
        $this->assertSame('foo', $details->synopsis);
        /* the query should not be executed more than once */
        $book->getRelation('details');
    }

    public function testGetRelationBelongsTo()
    {
        $details = new BookDetails($this->conn, ['books_id' => 5]);
        $driver = $this->expectDriver();
        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
                          ->disableOriginalConstructor()
                          ->getMock();
        $this->conn->expects($this->once())
               ->method('prepare')
               ->with('SELECT * FROM `books` WHERE `id` = ? LIMIT 1')
               ->will($this->returnValue($statement));
        $statement->expects($this->once())
                  ->method('execute')
                  ->with([5]);
        $result = ['name' => 'foo'];
        $statement->expects($this->once())
                  ->method('fetch')
                  ->will($this->returnValue($result));

        $book = $details->getRelation('book');
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('foo', $book->name);
        /* the query should not be executed more than once */
        $details->getRelation('book');
    }

    public function testSetRelation()
    {
        $book = new Book($this->conn);
        $details = new BookDetails($this->conn);
        $book->setRelation('details', $details);
        $this->assertSame($details, $book->getRelation('details'));
    }

    public function testGetRelationDefintions()
    {
        $expected = [
            'author' => [
                'belongs_to', 'ActiveDoctrine\Tests\Fixtures\Bookshop\Author', 'id', 'authors_id'
            ],
            'details' => [
                'has_one', 'ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails', 'books_id', 'id'
            ],
            'invalid' => 'foo'
        ];
        $this->assertSame($expected, Book::getRelationDefinitions());
    }

    public function testGetRelationDefintion()
    {
        $relation = Book::getRelationDefinition('details');
        $this->assertSame(['has_one', 'ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails', 'books_id', 'id'], $relation);
    }

    public function testGetRelationDefintionThrowsExceptionOnUnknownRelation()
    {
        $msg = 'Relation "unknown" of Entity "ActiveDoctrine\Tests\Fixtures\Bookshop\Book" is not defined';
        $this->setExpectedException('\Exception', $msg);
        Book::getRelationDefinition('unknown');
    }

    public function testGetRelationDefintionThrowsExceptionOnInvalidRelation()
    {
        $msg = 'Relation "invalid" of Entity "ActiveDoctrine\Tests\Fixtures\Bookshop\Book" is invalid';
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
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $first);
        $this->assertSame('foo', $first->name);

        $second = $books[1];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $second);
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

    public function testSetRawCallsSetRelation()
    {
        $book = new Book($this->conn, ['id' => 5]);
        $details = new BookDetails($this->conn, ['books_id' => 3]);
        $book->setRaw('details', $details);
        $this->assertSame($details, $book->getRelation('details'));
        $this->assertSame(5, $details->books_id);
    }

    public function testMagicIsset()
    {
        $book = new Book($this->conn, ['id' => 5]);
        $this->assertTrue(isset($book->id));
        $this->assertTrue(isset($book->title));
        $this->assertTrue(isset($book->author));
        $this->assertTrue(isset($book->not_a_column));
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
        $books = new EntityCollection();
        $author = new Author($this->conn);
        $author->setRelation('books', $books);
        $this->assertFalse($author->has('books'));

        //object collection containing entities
        $books[] = $book;
        $this->assertTrue($author->has('books'));
    }

    public function testHasRelation()
    {
        $book = new Book($this->conn);

        //avoid mocking a database call by setting a relation to null,
        //which is what an empty database result would do
        $book->setRelation('author', null);

        $this->assertFalse($book->hasRelation('author'));

        $book->setRelation('author', new Author($this->conn));
        $this->assertTrue($book->hasRelation('author'));
    }

    public function testHasRelationThrowsExceptionForInvalidName()
    {
        $book = new Book($this->conn);
        $this->setExpectedException('\Exception');
        $book->hasRelation('foo');
    }

    public function testAssociateRelationHasOne()
    {
        $book = new Book($this->conn, ['id' => 3]);
        $details = new BookDetails($this->conn, ['books_id' => 5]);
        $this->assertSame(3, $book->id);
        $this->assertSame(5, $details->books_id);
        $book->associateRelation('details', $details);
        $this->assertSame(3, $book->id);
        $this->assertSame(3, $details->books_id);
    }

    public function testAssociateRelationHasMany()
    {
        $author = new Author($this->conn, ['id' => 1]);
        $author->setStored();
        $book1 = new Book($this->conn, ['authors_id' => 3]);
        $book1->setStored();
        $book2 = new Book($this->conn, ['authors_id' => 5]);
        $book2->setStored();
        $book3 = new Book($this->conn, ['authors_id' => 1]);
        $book3->setStored();
        $this->assertSame(1, $author->id);
        $this->assertSame(3, $book1->authors_id);
        $this->assertSame(5, $book2->authors_id);
        $this->assertSame(1, $book3->authors_id);
        $author->associateRelation('books', new EntityCollection([$book1, $book2, $book3]));
        $this->assertSame(1, $author->id);
        $this->assertSame(1, $book1->authors_id);
        $this->assertSame(1, $book2->authors_id);
        $this->assertSame(1, $book3->authors_id);

        $this->assertFalse($author->isModified());
        $this->assertTrue($book1->isModified());
        $this->assertTrue($book2->isModified());
        $this->assertFalse($book3->isModified());
    }

    public function testAssociateRelationBelongsTo()
    {
        $book = new Book($this->conn, ['id' => 3]);
        $details = new BookDetails($this->conn, ['books_id' => 5]);
        $this->assertSame(3, $book->id);
        $this->assertSame(5, $details->books_id);
        $details->associateRelation('book', $book);
        $this->assertSame(3, $book->id);
        $this->assertSame(3, $details->books_id);
        $this->assertSame($book, $details->book);
    }

    public function unsetRelationProvider()
    {
        return [
            [0],
            [false],
            [null],
        ];
    }

    /**
     * @dataProvider unsetRelationProvider
     */
    public function testUnsetRelationHasOne($value)
    {
        $book = new Book($this->conn, ['id' => 3]);
        $details = new BookDetails($this->conn, ['books_id' => 5]);
        $book->associateRelation('details', $details);
        $this->assertSame(3, $details->books_id);
        $this->assertSame($details, $book->details);

        $book->unsetRelation('details', $value);
        $this->assertSame($value, $details->books_id);
        $this->assertFalse($book->details);
    }

    /**
     * @dataProvider unsetRelationProvider
     */
    public function testUnsetRelationBelongsTo($value)
    {
        $book = new Book($this->conn, ['id' => 3]);
        $details = new BookDetails($this->conn, ['books_id' => 5]);
        $details->associateRelation('book', $book);
        $this->assertSame(3, $details->books_id);
        $this->assertSame($book, $details->book);

        $details->unsetRelation('book', $value);
        $this->assertSame($value, $details->books_id);
        $this->assertFalse($details->book);
    }

    /**
     * @dataProvider unsetRelationProvider
     */
    public function testUnsetRelationHasMany($value)
    {
        $author = new Author($this->conn, ['id' => 3]);
        $book = new Book($this->conn, ['authors_id' => 5]);
        $books = new EntityCollection([$book]);

        $author->associateRelation('books', $books);
        $this->assertSame(3, $book->authors_id);
        $this->assertSame($books, $author->books);

        $author->unsetRelation('books', $value);
        $this->assertSame($value, $book->authors_id);
        $no_books = $author->books;

        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $no_books);
        $this->assertSame(0, count($no_books));
        $this->assertNotSame($books, $no_books);
    }

    public function testSerialize()
    {
        $book = new Book($this->conn, ['id' => 3, 'name' => 'foo']);
        $details = new BookDetails($this->conn, ['books_id' => 3]);
        $book->setRelation('details', $details);

        $stored = serialize($book);
        $fetched = unserialize($stored);
        $this->assertSame($book->getValues(), $fetched->getValues());
        $this->assertSame($details->getValues(), $fetched->details->getValues());
    }

    public function testSetAndGetConection()
    {
        $book = new Book($this->conn);
        $this->assertSame($this->conn, $book->getConnection());

        $conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();
        $this->assertSame($book, $book->setConnection($conn));
        $this->assertSame($conn, $book->getConnection());
    }

    public function testAddEventCallback()
    {
        $book_class = 'ActiveDoctrine\Tests\Fixtures\Bookshop\Book';
        $callbacks = new \ReflectionProperty($book_class, 'callbacks');
        $callbacks->setAccessible(true);

        //this is a bit of a hack - reset the callbacks, but only for $book_class
        $value = $callbacks->getValue();
        unset($value[$book_class]);
        $callbacks->setValue($value);

        $foo = function () {};

        Book::addEventCallBack('foo_event', $foo);
        $this->assertSame(['foo_event' => [$foo]], $callbacks->getValue()[$book_class]);

        Book::addEventCallBack('foo_event', $foo);
        $this->assertSame(['foo_event' => [$foo, $foo]], $callbacks->getValue()[$book_class]);

        Book::resetEventCallbacks();
    }

    public function testCallEvent()
    {
        $book = new Book($this->conn);
        $this->assertNull($book->foo);

        Book::addEventCallBack('foo_event', function ($book) {
            $book->foo = 'bar';
        });

        $book->callEvent('foo_event');
        $this->assertSame('bar', $book->foo);

        Book::resetEventCallbacks();
    }

    public function testResetEventCallbacks()
    {
        Book::addEventCallBack('foo_event', function ($book) {
            $book->foo = 'bar';
        });

        $book = new Book($this->conn);
        $book->foo = 'foo';
        $book->callEvent('foo_event');
        $this->assertSame('bar', $book->foo);

        Book::resetEventCallbacks();

        $book->foo = 'foo';
        $book->callEvent('foo_event');
        $this->assertSame('foo', $book->foo);
    }

    public function testEventCallbacksAreClassSpecific()
    {
        Book::resetEventCallbacks();
        Author::resetEventCallbacks();
        $foo = function () {};
        $bar = function () {};

        Book::addEventCallBack('insert', $foo);
        Author::addEventCallBack('update', $bar);

        $callbacks = new \ReflectionProperty('ActiveDoctrine\Entity\Entity', 'callbacks');
        $callbacks->setAccessible(true);

        $this->assertSame([
            'insert' => [$foo]
        ], $callbacks->getValue()['ActiveDoctrine\Tests\Fixtures\Bookshop\Book']);
        $this->assertSame([
            'update' => [$bar]
        ], $callbacks->getValue()['ActiveDoctrine\Tests\Fixtures\Bookshop\Author']);
    }
}
