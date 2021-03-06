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
            $field_settings = $classname::getFieldSettings();

            foreach ($classname::getFields() as $field) {
                $type = isset($types[$field]) ? $types[$field] : 'string';

                if ($field === $classname::getPrimaryKeyName() && !isset($types[$field])) {
                    $type = 'integer';
                }

                $settings = isset($field_settings[$field]) ? $field_settings[$field] : [];

                $column = $table->addColumn($field, $type, $settings);

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
        if (!is_dir($directory)) {
            return;
        }
        $files = new \DirectoryIterator($directory);

        foreach ($files as $file) {
            if ($file->isDir() && !$file->isDot()) {
                $this->addEntityDirectory($namespace.'\\'.basename($file->getPathname()) , $file->getPathname());
                continue;
            }

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
