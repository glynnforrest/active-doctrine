<?php

namespace ActiveDoctrine\Schema;

use Doctrine\DBAL\Schema\Schema;

/**
 * SchemaCreator
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SchemaCreator
{
    protected $classes = [];

    /**
     * @return Schema
     */
    public function createSchema()
    {
        $schema = new Schema();
        foreach ($this->classes as $classname) {
            $table = $schema->createTable($classname::getTable());
            foreach ($classname::getFields() as $field) {
                $table->addColumn($field, 'text');
            }
        }

        return $schema;
    }

    /**
     * @param string $classname Entity class
     */
    public function addEntityClass($classname)
    {
        $this->classes[] = $classname;
    }
}
