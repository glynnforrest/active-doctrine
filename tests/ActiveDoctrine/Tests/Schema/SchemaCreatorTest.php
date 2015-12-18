<?php

namespace ActiveDoctrine\Tests\Schema;

use ActiveDoctrine\Schema\SchemaCreator;
use ActiveDoctrine\Tests\Fixtures\Articles\Article;

/**
 * SchemaCreatorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SchemaCreatorTest extends \PHPUnit_Framework_TestCase
{
    protected $schema;

    public function setUp()
    {
        $this->creator = new SchemaCreator();
    }

    public function testCreateEmptySchema()
    {
        $this->assertInstanceOf('Doctrine\DBAL\Schema\Schema', $this->creator->createSchema());
    }

    public function testSchemaWithOneEntity()
    {
        $this->creator->addEntityClass('ActiveDoctrine\Tests\Fixtures\Articles\Article');
        $schema = $this->creator->createSchema();
        $this->assertTrue($schema->hasTable('articles'));

        $table = $schema->getTable('articles');

        $this->assertSame(Article::getFields(), array_keys($table->getColumns()));

        $this->assertSame('integer', $table->getColumn('id')->getType()->getName());
        $this->assertSame('datetime', $table->getColumn('created_at')->getType()->getName());
    }

    public function testPrimaryKeyDefaultsToIncrementingInteger()
    {
        $this->creator->addEntityClass('ActiveDoctrine\Tests\Fixtures\Misc\NoTypes');
        $schema = $this->creator->createSchema();
        $this->assertTrue($schema->hasTable('no_types'));
        $table = $schema->getTable('no_types');

        $this->assertTrue($table->hasColumn('id'));
        $id = $table->getColumn('id');
        $this->assertSame('integer', $id->getType()->getName());
        $this->assertTrue($id->getAutoincrement());
        $this->assertSame(['id'], $table->getPrimaryKey()->getColumns());
    }
}
