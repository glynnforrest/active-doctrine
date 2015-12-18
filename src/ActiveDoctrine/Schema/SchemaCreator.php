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
            $types = $classname::getTypes();
            foreach ($classname::getFields() as $field) {
                $type = isset($types[$field]) ? $types[$field] : 'text';

                if ($field === $classname::getPrimaryKeyName() && !isset($types[$field])) {
                    $type = 'integer';
                }

                $column = $table->addColumn($field, $type);

                if ($field === $classname::getPrimaryKeyName()) {
                    $column->setAutoincrement(true);
                    $table->setPrimaryKey([$field]);
                }
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
