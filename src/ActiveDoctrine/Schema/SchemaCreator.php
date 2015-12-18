<?php

namespace ActiveDoctrine\Schema;

use Doctrine\DBAL\Schema\Schema;

/**
 * Create schemas from entity definitions.
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

    /**
     * Add all entity classes in a directory.
     *
     * @param string $namespace The base namespace of the entities
     * @param string $directory The directory
     */
    public function addEntityDirectory($namespace, $directory)
    {
        $files = new \DirectoryIterator($directory);

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $class = $namespace.'\\'.$file->getBasename('.php');
            $r = new \ReflectionClass($class);
            if ($r->isSubclassOf('ActiveDoctrine\Entity\Entity') && !$r->isAbstract()) {
                $this->addEntityClass($class);
            }
        }
    }
}
