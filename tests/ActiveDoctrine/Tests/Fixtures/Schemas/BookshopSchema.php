<?php

namespace ActiveDoctrine\Tests\Fixtures\Schemas;

use Doctrine\DBAL\Schema\Schema;

/**
 * BookshopSchema
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class BookshopSchema implements SchemaInterface
{

    public function up(Schema $schema)
    {
        $table = $schema->createTable('books');
        $id = $table->addColumn('id', 'integer', ['unsigned' => true]);
        $id->setAutoIncrement(true);
        $table->setPrimaryKey(['id']);
        $table->addColumn('name', 'string', ['length' => '255']);
        $table->addColumn('description', 'text');
        $id = $table->addColumn('authors_id', 'integer', ['unsigned' => true]);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('books');
    }

}
