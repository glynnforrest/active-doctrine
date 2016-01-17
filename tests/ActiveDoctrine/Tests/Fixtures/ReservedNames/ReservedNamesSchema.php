<?php

namespace ActiveDoctrine\Tests\Fixtures\ReservedNames;

use Doctrine\DBAL\Schema\Schema;
use ActiveDoctrine\Tests\Fixtures\SchemaInterface;

/**
 * ReservedNamesSchema
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ReservedNamesSchema implements SchemaInterface
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('`insert`');
        $id = $table->addColumn('id', 'integer', ['unsigned' => true]);
        $id->setAutoIncrement(true);
        $table->setPrimaryKey(['id']);
        $table->addColumn('name', 'string', ['length' => 255]);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('`insert`');
    }
}
