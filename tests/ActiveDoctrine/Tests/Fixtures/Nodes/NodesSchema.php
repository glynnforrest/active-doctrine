<?php

namespace ActiveDoctrine\Tests\Fixtures\Nodes;

use Doctrine\DBAL\Schema\Schema;
use ActiveDoctrine\Tests\Fixtures\SchemaInterface;

/**
 * NodesSchema
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class NodesSchema implements SchemaInterface
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('nodes');
        $id = $table->addColumn('id', 'integer', ['unsigned' => true]);
        $id->setAutoIncrement(true);
        $table->setPrimaryKey(['id']);
        $table->addColumn('parent_id', 'integer', ['unsigned' => true, 'default' => 0, 'notnull' => false]);
        $table->addColumn('value', 'string');
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('nodes');
    }
}
