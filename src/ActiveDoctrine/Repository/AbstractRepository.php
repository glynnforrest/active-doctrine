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
     * Find all Entities.
     *
     * @return EntityCollection A collection of entities
     */
    public function findAll()
    {
        return $this->selector()->execute();
    }
}
