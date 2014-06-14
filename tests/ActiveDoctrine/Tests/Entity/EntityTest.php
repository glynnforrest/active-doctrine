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

    public function testInsert()
    {
        $obj = new Book($this->conn);

        $obj->name = 'foo';
        $obj->author = 'bar';

        $this->conn->expects($this->once())
                   ->method('insert')
                   ->with('books', ['name' => 'FOO', 'author' => 'bar']);

        $obj->insert();
    }

}
