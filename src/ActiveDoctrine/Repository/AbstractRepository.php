<?php

namespace ActiveDoctrine\Repository;

use Doctrine\DBAL\Connection;

/**
 * AbstractRepository
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractRepository
{
    protected $conn;
    protected $entity_class;

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }

    /**
     * Create a new Entity.
     *
     * @return Entity
     */
    public function create()
    {
        $class = $this->entity_class;

        return new $class($this->conn);
    }

    protected function selector()
    {
        $class = $this->entity_class;

        return $class::select($this->conn);
    }

    /**
     * Select all entities.
     *
     * @return EntityCollection The selected entities
     */
    public function findAll()
    {
        return $this->selector()->execute();
    }

    /**
     * Select entities matching an array of conditions.
     *
     * @param  array            $conditions An array of the form 'column => value'
     * @return EntityCollection The selected entities
     */
    public function findBy(array $conditions)
    {
        $s = $this->selector();
        foreach ($conditions as $column => $value) {
            $s->where($column, '=', $value);
        }

        return $s->execute();
    }

    /**
     * Select a single entity matching an array of conditions.
     *
     * @param  array       $conditions An array of the form 'column => value'
     * @return Entity|null The selected entity or null
     */
    public function findOneBy(array $conditions)
    {
        $s = $this->selector()->one();
        foreach ($conditions as $column => $value) {
            $s->where($column, '=', $value);
        }

        return $s->execute();
    }
}
