<?php

namespace ActiveDoctrine\Entity;

use \Iterator;
use \Countable;
use \ArrayAccess;

/**
 * EntityCollection
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EntityCollection implements Iterator, Countable, ArrayAccess
{

    protected $entities;
    protected $position;

    public function __construct(array $entities = [])
    {
        $this->entities = $entities;
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
     * @return array An array of entities
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Return the entities in this collection, split into chunks.
     *
     * @param  int   $size The size of each chunk
     * @return array An array of entities
     */
    public function getEntitiesChunked($chunk)
    {
        return array_chunk($this->entities, $chunk);
    }

    /**
     * Set the value of a column for all entities in this collection.
     *
     * @param  string           $name  The name of the column
     * @param  mixed            $value The value
     * @return EntityCollection This collection
     */
    public function setColumn($name, $value)
    {
        foreach ($this->entities as $entity) {
            $entity->set($name, $value);
        }
    }

    /**
     * Get the values of a single key from all entities in this collection.
     *
     * @param  string $name The name of the column
     * @return array  A list of values
     */
    public function getColumn($name)
    {
        $results = [];
        foreach ($this->entities as $entity) {
            $results[] = $entity->get($name);
        }

        return $results;
    }

    /**
     * Save all the entities in this collection.
     *
     * @return EntityCollection This collection
     */
    public function save()
    {
        foreach ($this->entities as $entity) {
            $entity->save();
        }

        return $this;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->entities[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function valid()
    {
        return isset($this->entities[$this->position]);
    }

    public function count()
    {
        return count($this->entities);
    }

    public function offsetGet($offset)
    {
        return isset($this->entities[$offset]) ? $this->entities[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (!is_int($offset) && !is_null($offset)) {
            throw new \InvalidArgumentException('Non numeric keys for EntityCollection are forbidden.');
        }
        if (is_null($offset)) {
            $this->entities[] = $value;
        } else {
            $this->entities[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->entities[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->entities[$offset]);
    }

}
