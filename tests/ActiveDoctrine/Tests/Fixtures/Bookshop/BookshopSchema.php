<?php

namespace ActiveDoctrine\Tests\Fixtures\Bookshop;

use Doctrine\DBAL\Schema\Schema;
use ActiveDoctrine\Tests\Fixtures\SchemaInterface;

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

        $table = $schema->createTable('book_details');
        $id = $table->addColumn('id', 'integer', ['unsigned' => true]);
        $id->setAutoIncrement(true);
        $table->setPrimaryKey(['id']);
        $table->addColumn('books_id', 'integer', ['unsigned' => true]);
        $table->addColumn('synopsis', 'text');
        $table->addColumn('pages', 'integer', ['unsigned' => true]);
        $table->addColumn('chapters', 'integer', ['unsigned' => true]);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('books');
        $schema->dropTable('book_details');
    }

}
