<?php

namespace ActiveDoctrine\Tests\Fixtures;

use Doctrine\DBAL\Schema\Schema;

/**
 * SchemaInterface
 *
 * These classes are responsible for creating and removing tables in
 * the test database.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface SchemaInterface
{

    public function up(Schema $schema);

    public function down(Schema $schema);

}
