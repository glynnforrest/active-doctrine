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

    public function testColumnsDefaultToString()
    {
        $this->creator->addEntityClass('ActiveDoctrine\Tests\Fixtures\Misc\NoTypes');
        $schema = $this->creator->createSchema();
        $this->assertTrue($schema->hasTable('no_types'));
        $table = $schema->getTable('no_types');

        $this->assertSame('string', $table->getColumn('name')->getType()->getName());
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

    public function testFieldSettingsAreUsed()
    {
        $this->creator->addEntityClass('ActiveDoctrine\Tests\Fixtures\Articles\Article');
        $schema = $this->creator->createSchema();

        $table = $schema->getTable('articles');
        $this->assertSame(5, $table->getColumn('id')->getLength());
    }

    public function testAddEntityDirectory()
    {
        $this->creator->addEntityDirectory('ActiveDoctrine\Tests\Fixtures\Misc', __DIR__.'/../Fixtures/Misc');
        $schema = $this->creator->createSchema();

        $this->assertTrue($schema->hasTable('no_types'));
        $table = $schema->getTable('no_types');
        $this->assertTrue($table->hasColumn('id'));

        $this->assertTrue($schema->hasTable('multi_slug'));
        $table = $schema->getTable('multi_slug');
        $this->assertTrue($table->hasColumn('foo_slug'));
    }

    public function testAddEntityDirectoryRecursive()
    {
        $this->creator->addEntityDirectory('ActiveDoctrine\Tests\Fixtures', __DIR__.'/../Fixtures');
        $schema = $this->creator->createSchema();

        $this->assertTrue($schema->hasTable('no_types'));
        $table = $schema->getTable('no_types');
        $this->assertTrue($table->hasColumn('id'));

        $this->assertTrue($schema->hasTable('books'));
        $table = $schema->getTable('books');
        $this->assertTrue($table->hasColumn('name'));

        $this->assertTrue($schema->hasTable('performances'));
        $table = $schema->getTable('performances');
        $this->assertTrue($table->hasColumn('start_time'));

        $this->assertTrue($schema->hasTable('records'));
        $table = $schema->getTable('records');
        $this->assertTrue($table->hasColumn('description'));

        $this->assertTrue($schema->hasTable('nested'));
        $table = $schema->getTable('nested');
        $this->assertTrue($table->hasColumn('something'));
        $this->assertSame('datetime', $table->getColumn('something')->getType()->getName());

        $this->assertTrue($schema->hasTable('really_nested'));
        $table = $schema->getTable('really_nested');
        $this->assertTrue($table->hasColumn('something_else'));
        $this->assertSame('float', $table->getColumn('something_else')->getType()->getName());
    }
}
