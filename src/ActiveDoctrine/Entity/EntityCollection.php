<?php

namespace ActiveDoctrine\Entity;

use Doctrine\DBAL\Connection;

/**
 * EntityCollection
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EntityCollection
{

    protected $connection;
    protected $entities = array();
    protected $table;
    protected $primary_key;
    protected $fields = array();
    protected $entity_class;

    public function __construct(Connection $connection, array $entities = array())
    {
        $this->connection = $connection;
        $this->entities = $entities;
    }

    /**
     * Set the name of the database table of the entities in this
     * collection.
     *
     * @param  string           $table The name of the table
     * @return EntityCollection This collection
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the name of the database table of the entities in this
     * collection.
     *
     * @return string The name of the table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set the database fields of the entities in this collection.
     *
     * @param  array            $fields The list of fields
     * @return EntityCollection This collection
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get the database fields of the entities in this collection.
     *
     * @return array The list of fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set the name of the primary key of the entities in this
     * collection.
     *
     * @param  string           $name The name of the key
     * @return EntityCollection This collection
     */
    public function setPrimaryKey($name)
    {
        $this->primary_key = $name;

        return $this;
    }

    /**
     * Get the name of the primary key of the entities in this
     * collection.
     *
     * @return string The name of the key
     */
    public function getPrimaryKey()
    {
        return $this->primary_key;
    }

    /**
     * Set the class name of the entities in this collection.
     *
     * @param  string           $class The class name
     * @return EntityCollection This collection
     */
    public function setEntityClass($class)
    {
        $this->entity_class = $class;

        return $this;
    }

    /**
     * Get the class name of the entities in this collection.
     *
     * @return string The class name
     */
    public function getEntityClass()
    {
        return $this->entity_class;
    }

    /**
     * Set the entities in this collection.
     *
     * @return array
     * @return EntityCollection This collection
     */
    public function setEntities(array $entities = array())
    {
        $this->entities = $entities;

        return $this;
    }

    /**
     * Return the entities in this collection.
     *
     * @return array The array of entities
     */
    public function getEntities()
    {
        return $this->entities;
    }

}
