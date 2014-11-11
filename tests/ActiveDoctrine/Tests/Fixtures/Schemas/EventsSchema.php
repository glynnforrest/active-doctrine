<?php

namespace ActiveDoctrine\Tests\Fixtures\Schemas;

use Doctrine\DBAL\Schema\Schema;

/**
 * EventsSchema
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EventsSchema implements SchemaInterface
{

    public function up(Schema $schema)
    {
        $table = $schema->createTable('events');
        $id = $table->addColumn('id', 'integer', ['unsigned' => true]);
        $id->setAutoIncrement(true);
        $table->setPrimaryKey(['id']);
        $table->addColumn('start_time', 'datetime');
        $table->addColumn('name', 'string', ['length' => 255]);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('events');
    }

}
