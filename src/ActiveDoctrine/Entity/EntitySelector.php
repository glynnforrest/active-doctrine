<?php

namespace ActiveDoctrine\Entity;

use Doctrine\DBAL\Connection;

use ActiveDoctrine\Selector\AbstractSelector;

/**
 * EntitySelector
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EntitySelector
{

    protected $entity_class;
    protected $connection;
    protected $selector;
    protected $single;

    public function __construct(Connection $connection, $entity_class, $table)
    {
        $this->entity_class = $entity_class;
        $this->connection = $connection;
        $this->selector = AbstractSelector::fromConnection($connection, $table);
    }

    /**
     * Call the underlying selector.
     *
     * @param string $method The method
     * @param array  $args   The arguments
     */
    public function __call($method, array $args)
    {
        call_user_func_array(array($this->selector, $method), $args);

        return $this;
    }

    /**
     * Get the underlying SQL query for this EntitySelector.
     *
     * @return string The SQL query
     */
    public function getSQL()
    {
        return $this->selector->getSQL();
    }

    public function one()
    {
        $this->limit(1);
        $this->single = true;

        return $this;
    }

    /**
     * Execute the query and return the selected entities.
     *
     * @return EntityCollection The collection of selected entities
     */
    public function execute()
    {
        $class = $this->entity_class;

        if ($this->single) {
            return $class::selectOneSQL($this->connection, $this->selector->getSQL(), $this->selector->getParams());
        }

        $collection = $class::selectSQL($this->connection, $this->selector->getSQL(), $this->selector->getParams());

        return $collection;
    }

}
