<?php

namespace ActiveDoctrine\Tests\Fixtures\Articles;

use Doctrine\DBAL\Schema\Schema;
use ActiveDoctrine\Tests\Fixtures\SchemaInterface;

/**
 * ArticlesSchema
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ArticlesSchema implements SchemaInterface
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('articles');
        $id = $table->addColumn('id', 'integer', ['unsigned' => true]);
        $id->setAutoIncrement(true);
        $table->setPrimaryKey(['id']);
        $table->addColumn('title', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        $table = $schema->createTable('writers');
        $id = $table->addColumn('id', 'integer', ['unsigned' => true]);
        $id->setAutoIncrement(true);
        $table->setPrimaryKey(['id']);
        $table->addColumn('createdAt', 'datetime');
        $table->addColumn('anotherCreate', 'datetime');
        $table->addColumn('updatedAt', 'datetime');
        $table->addColumn('anotherUpdate', 'datetime');
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('articles');
        $schema->dropTable('writers');
    }
}
