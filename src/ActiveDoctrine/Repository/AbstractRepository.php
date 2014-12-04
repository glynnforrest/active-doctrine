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
    protected $entity_class = 'unknown';

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }

    public function create()
    {
        $class = $this->entity_class;

        return new $class($this->conn);
    }
}
