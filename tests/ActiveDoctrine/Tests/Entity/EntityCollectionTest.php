<?php

namespace ActiveDoctrine\Tests\Entity;

use ActiveDoctrine\Entity\EntityCollection;

/**
 * EntityCollectionTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EntityCollectionTest extends \PHPUnit_Framework_TestCase
{

    protected $conn;

    public function setUp()
    {
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                           ->disableOriginalConstructor()
                           ->getMock();
    }

    public function testSetAndGetTable()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertNull($collection->getTable());
        $this->assertSame($collection, $collection->setTable('foo'));
        $this->assertSame('foo', $collection->getTable());
    }

    public function testSetAndGetFields()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertSame(array(), $collection->getFields());
        $this->assertSame($collection, $collection->setFields(array('id', 'foo', 'bar')));
        $this->assertSame(array('id', 'foo', 'bar'), $collection->getFields());
    }

    public function testSetAndGetPrimaryKey()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertNull($collection->getPrimaryKey());
        $this->assertSame($collection, $collection->setPrimaryKey('id'));
        $this->assertSame('id', $collection->getPrimaryKey());
    }

    public function testSetAndGetEntityClass()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertNull($collection->getEntityClass());
        $this->assertSame($collection, $collection->setEntityClass('ActiveDoctrine\Tests\Entity\Book'));
        $this->assertSame('ActiveDoctrine\Tests\Entity\Book', $collection->getEntityClass());
    }

    public function testSetAndGetEntities()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertSame(array(), $collection->getEntities());
        $entities = array(new Book($this->conn), new Book($this->conn));
        $this->assertSame($collection, $collection->setEntities($entities));
        $this->assertSame($entities, $collection->getEntities());
    }

    public function testAddEntitiesOnConstruct()
    {
        $entities = array(new Book($this->conn), new Book($this->conn));
        $collection = new EntityCollection($this->conn, $entities);
        $this->assertSame($entities, $collection->getEntities());
    }

}
