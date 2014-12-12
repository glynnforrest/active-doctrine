<?php

namespace ActiveDoctrine\Tests\Fixtures\MusicFestival;

use Doctrine\DBAL\Schema\Schema;
use ActiveDoctrine\Tests\Fixtures\SchemaInterface;

/**
 * MusicFestivalSchema
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MusicFestivalSchema implements SchemaInterface
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('performances');
        $id = $table->addColumn('id', 'integer', ['unsigned' => true]);
        $id->setAutoIncrement(true);
        $table->setPrimaryKey(['id']);
        $table->addColumn('start_time', 'datetime');
        $table->addColumn('name', 'string', ['length' => 255]);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('performances');
    }
}
