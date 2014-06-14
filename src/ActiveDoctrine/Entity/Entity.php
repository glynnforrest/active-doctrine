<?php

namespace ActiveDoctrine\Entity;

use Doctrine\DBAL\Connection;

/**
 * Entity
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class Entity
{

    protected static $table;
    protected static $primary_key = 'id';
    protected static $fields = [];

    protected $connection;
    protected $values = [];
    protected $modified = [];
    protected $stored;
    protected $current_index;

    public function __construct(Connection $connection, array $values = array())
    {
        $this->connection = $connection;
        $this->values = $values;

        //keep a copy of the primary key for updating in case it changes
        if (isset($values[static::$primary_key])) {
            $this->current_index = $this->values[static::$primary_key];
        }
    }

    /**
     * Convenience wrapper to get().
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Get the value of $key. If the method getter<$key> exists, the return
     * value will be the output of calling this function.
     *
     * @param string $key The name of the key to get.
     */
    public function get($key)
    {
        $method = 'getter' . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return $this->getRaw($key);
    }

    /**
     * Get the value of $key. If $key doesn't exist, null will be
     * returned.
     *
     * @param string $key The name of the key to get.
     */
    public function getRaw($key)
    {
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    /**
     * Get all values. Getter methods will be called on the values.
     *
     * @return array The values
     */
    public function getValues()
    {
        $return = array();
        foreach ($this->values as $k => $v) {
            $return[$k] = $this->get($k);
        }

        return $return;
    }

    /**
     * Get all values. Getter methods will not be called on the
     * values.
     *
     * @return array The values
     */
    public function getValuesRaw()
    {
        return $this->values;
    }

    /**
     * Convenience wrapper to set().
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Set field $key to $value. If the method setter<Key> exists, $value will
     * be the output of calling this function with $value as an
     * argument.
     *
     * @param string $key   The name of the key to set.
     * @param mixed  $value The value to set.
     */
    public function set($key, $value)
    {
        $method = 'setter' . ucfirst($key);
        if (method_exists($this, $method)) {
            $value = $this->$method($value);
        }
        $this->setRaw($key, $value);
    }

    /**
     * Set field $key to $value, ignoring any setter methods.
     *
     * @param string $key   The name of the key to set.
     * @param mixed  $value The value to set.
     */
    public function setRaw($key, $value)
    {
        //apply the modified flag if the key is one of the fields and
        //the value has changed
        if (in_array($key, static::$fields) && $value !== $this->getRaw($key)) {
            $this->modified[$key] = true;
        }
        $this->values[$key] = $value;
    }

    /**
     * Set an array of values. Setter methods will be called if they
     * exist.
     *
     * @param array $values The array of values to set
     */
    public function setValues(array $values = array())
    {
        foreach ($values as $k => $v) {
            $this->set($k, $v);
        }

        return $this;
    }

    /**
     * Set an array of values. Setter methods will not be called.
     *
     * @param array $values The array of values to set
     */
    public function setValuesRaw($values = array())
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    /**
     * Get a list of modified fields.
     *
     * @return array The modified fields
     */
    public function getModifiedFields()
    {
        return array_keys($this->modified);
    }

    /**
     * Persist this entity to the database using an insert query.
     */
    public function insert()
    {
        if ($this->stored) {
            throw new \LogicException("You may not insert an already stored entity");
        }

        if (empty($this->modified)) {
            return;
        }

        $values = array_intersect_key($this->values, $this->modified);
        $this->connection->insert(static::$table, $values);
        $this->modified = array();
        //this will only work with some database vendors for now.
        $this->values[static::$primary_key] = $this->connection->lastInsertId();
        $this->stored = true;
    }

    /**
     * Get the primary key for this entity as it is stored in the
     * database. If the key has been updated but not saved, the
     * original value will be returned.
     *
     * @return string The primary key of the entity
     */
    protected function getPrimaryKey()
    {
        if (!isset($this->values[static::$primary_key])) {
            throw new \LogicException('Primary key not set');
        }

        return $this->current_index ?: $this->values[static::$primary_key];
    }

    /**
     * Update this entity in the database.
     */
    public function update()
    {
        if (empty($this->modified)) {
            return;
        }
        $values = array_intersect_key($this->values, $this->modified);
        $where = [static::$primary_key => $this->getPrimaryKey()];
        $this->connection->update(static::$table, $values, $where);
        $this->modified = [];
    }

    /**
     * Delete this entity from the database.
     */
    public function delete()
    {
        $where = [static::$primary_key => $this->getPrimaryKey()];

        return $this->connection->delete(static::$table, $where);
    }

}
