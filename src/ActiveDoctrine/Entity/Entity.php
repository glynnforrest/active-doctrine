<?php

namespace ActiveDoctrine\Entity;

use Doctrine\DBAL\Connection;
use ActiveDoctrine\Selector\AbstractSelector;

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
    protected static $relations = [];
    //blacklist is not an array to allow for an empty blacklist
    protected static $blacklist;
    protected static $types = [];
    protected static $callbacks = [];
    protected static $init = [];
    //used to save calculating the list of columns and relations every time
    protected static $column_cache = [];

    protected $connection;
    protected $values = [];
    protected $modified = [];
    protected $stored = false;
    protected $current_index;
    protected $relation_objects = [];

    public function __construct(Connection $connection, array $values = [])
    {
        $this->connection = $connection;
        $this->values = $values;
        //set modified for values in static::$fields
        $this->modified = array_fill_keys(array_intersect(array_keys($values), static::$fields), true);

        //keep a copy of the primary key for updating in case it changes
        if (isset($values[static::$primary_key])) {
            $this->current_index = $this->values[static::$primary_key];
        }

        if (!isset(static::$init[get_class($this)])) {
            $this->init();
            static::$init[get_class($this)] = true;
        }
    }

    protected function init()
    {
        foreach ($this->getClassParents(get_class($this)) as $class) {
            $method = 'init'.basename(str_replace('\\', '/', $class));
            if (method_exists($class, $method)) {
                forward_static_call([$this, $method]);
            }
        }

        static::$column_cache[get_class($this)] = array_fill_keys(array_merge(static::$fields, array_keys(static::$relations)), true);
    }

    protected function getClassParents($class)
    {
        return class_uses($class);
    }

    /**
     * Save a small subset of properties when serializing.
     */
    public function __sleep()
    {
        return [
            'values',
            'relation_objects',
            'stored',
        ];
    }

    /**
     * Set the database connection for this entity.
     *
     * @param Connection $connection
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Get the database connection for this entity.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the names of all fields.
     *
     * @return array The fields
     */
    public static function getFields()
    {
        return static::$fields;
    }

    /**
     * Get all relation definitions.
     *
     * @return array A list of all relation definitions
     */
    public static function getRelationDefinitions()
    {
        return static::$relations;
    }

    /**
     * Get the relation definition for a named relation. The
     * definition is a list with the form
     * [$type, $foreign_class, $foreign_column, $column].
     *
     * @param  string $name The name of the relation
     * @return array  The relation definition
     */
    public static function getRelationDefinition($name)
    {
        if (!isset(static::$relations[$name])) {
            throw new \Exception(sprintf('Relation "%s" of Entity "%s" is not defined', $name, get_called_class()));
        }
        $relation = static::$relations[$name];
        if (count($relation) !== 4) {
            throw new \Exception(sprintf('Relation "%s" of Entity "%s" is invalid', $name, get_called_class()));
        }

        return $relation;
    }

    /**
     * Get the entity table.
     *
     * @return string
     */
    public static function getTable()
    {
        return static::$table;
    }

    /**
     * Call an event on this entity, calling the registering
     * callbacks.
     *
     * @param string $event_name
     */
    public function callEvent($event_name)
    {
        $classname = get_called_class();
        if (!isset(static::$callbacks[$classname][$event_name])) {
            return;
        }

        foreach (static::$callbacks[$classname][$event_name] as $callback) {
            $callback($this);
        }
    }

    /**
     * Add a callback to run when calling an event on this entity.
     *
     * @param string   $event_name
     * @param \Closure $callback
     */
    public static function addEventCallBack($event_name, \Closure $callback)
    {
        static::$callbacks[get_called_class()][$event_name][] = $callback;
    }

    /**
     * Remove all event callbacks on this entity.
     */
    public static function resetEventCallbacks()
    {
        static::$callbacks[get_called_class()] = [];
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
     * Get the value of $key. If $key is the name of a relation, the
     * relation will be fetched. If $key doesn't exist, null will be
     * returned.
     *
     * @param string $key The name of the key to get.
     */
    public function getRaw($key)
    {
        if (isset(static::$relations[$key])) {
            return $this->getRelation($key);
        }

        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    /**
     * Get the named related object. If the database has not been
     * queried it will be fetched automatically. If the database has
     * been queried the original result will be returned.
     *
     * @param string $name The name of the relation
     */
    public function getRelation($name)
    {
        if (array_key_exists($name, $this->relation_objects)) {
            return $this->relation_objects[$name];
        }

        $relation = self::getRelationDefinition($name);

        $this->relation_objects[$name] = $this->fetchRelation($relation);

        return $this->relation_objects[$name];
    }

    /**
     * Check if this Entity has a value for $key. $key may be a simple
     * value, a related object, or a custom getter method.
     *
     * @param string $key The name of the key to check
     */
    public function has($key)
    {
        $result = $this->get($key);

        //an empty EntityCollection means no related entities
        if ($result instanceof EntityCollection && count($result) === 0) {
            return false;
        }

        return $result ? true : false;
    }

    /**
     * Check if this Entity has the named related object. If the
     * database has not been queried it will be checked automatically.
     *
     * @param string $name The name of the relation
     */
    public function hasRelation($name)
    {
        return $this->getRelation($name) ? true : false;
    }

    /**
     * Utility method for libraries that attempt to access object
     * properties, e.g. Twig. This always returns true as
     * ActiveDoctrine makes no distinction between
     * $entity->get('column') returning null and
     * $entity->get('not_a_column') returning null, so fields that are
     * not included in the table schema can be used. Use has() to
     * check if an entity column or relation has a value.
     *
     * @param string $key

     * @return bool
     */
    public function __isset($key)
    {
        return true;
    }

    /**
     * Fetch a related entity from the database.
     *
     * @param array $relation The relation to fetch.
     */
    protected function fetchRelation(array $relation)
    {
        /* a relation is of the form
         * [$type, $foreign_class, $foreign_column, $column]
         */
        list($type, $foreign_class, $foreign_column, $column) = $relation;

        switch ($type) {
        case 'has_one':
            return $this->fetchOneToOne($foreign_class, $foreign_column, $column);
        case 'belongs_to':
            return $this->fetchOneToOne($foreign_class, $foreign_column, $column);
        case 'has_many':
            return $this->fetchOneToMany($foreign_class, $foreign_column, $column);
        default:
        }
    }

    /**
     * Query the database for a one to one relationship.
     *
     * @param string $foreign_class  The class name of the related entity
     * @param string $foreign_column The name of the column on the other table
     * @param string $column         The name of column on this table
     */
    protected function fetchOneToOne($foreign_class, $foreign_column, $column)
    {
        return $foreign_class::selectOne($this->connection)
            ->where($foreign_column, '=', $this->getRaw($column))
            ->execute();
    }

    /**
     * Query the database for a one to many relationship.
     *
     * @param string $foreign_class  The class name of the related entity
     * @param string $foreign_column The name of the column on the other table
     * @param string $column         The name of column on this table
     */
    protected function fetchOneToMany($foreign_class, $foreign_column, $column)
    {
        return $foreign_class::select($this->connection)
            ->where($foreign_column, '=', $this->getRaw($column))
            ->execute();
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
     * Set the value of $key. If $key is the name of a relation, the
     * relation will associated with this entity.
     *
     * @param string $key   The name of the key to set.
     * @param mixed  $value The value to set.
     */
    public function setRaw($key, $value)
    {
        if (isset(static::$relations[$key])) {
            return $this->associateRelation($key, $value);
        }

        //apply the modified flag if the key is one of the fields and
        //the value has changed
        if (in_array($key, static::$fields) && $value !== $this->getRaw($key)) {
            $this->modified[$key] = true;
        }
        $this->values[$key] = $value;
    }

    /**
     * Set the named related object.
     *
     * @param string $name           The name of the relation
     * @param mixed  $related_object The related object
     */
    public function setRelation($name, $related_object)
    {
        //if no related object is supplied, set it to false (an array
        //value of null will not pass an isset() check on the
        //relation_objects array).
        if (!$related_object) {
            $related_object = false;
        }

        $this->relation_objects[$name] = $related_object;
    }

    /**
     * Set the named related object and ensure the joining columns are
     * matched. If $related_object is not an Entity or
     * EntityCollection, the relationship will be removed.
     *
     * @param string $name           The name of the relation
     * @param mixed  $related_object The related object
     */
    public function associateRelation($name, $related_object)
    {
        if (!$related_object instanceof Entity && !$related_object instanceof EntityCollection) {
            return $this->unsetRelation($name, $related_object);
        }

        list($type, $foreign_class, $foreign_column, $column) = static::getRelationDefinition($name);

        $this->setRelation($name, $related_object);

        if ($type === 'has_one') {
            $related_object->setRaw($foreign_column, $this->getRaw($column));
        }
        if ($type === 'belongs_to') {
            $this->setRaw($column, $related_object->getRaw($foreign_column));
        }
        if ($type === 'has_many') {
            $related_object->setColumn($foreign_column, $this->getRaw($column));
        }
    }

    /**
     * Unset a related object and assign the joining column a
     * value. If the related object has not been fetched, it will be
     * fetched and the joining column changed.
     *
     * @param string $name  The name of the relation
     * @param mixed  $value The value to assign to the joining column
     */
    public function unsetRelation($name, $value)
    {
        list($type, $foreign_class, $foreign_column, $column) = static::getRelationDefinition($name);

        //no need to get the relation, the column is on this object.
        if ($type === 'belongs_to') {
            $this->setRaw($column, $value);
            $this->relation_objects[$name] = false;

            return;
        }

        $related_object = $this->getRelation($name);

        if (!$related_object) {
            return;
        }

        //has one, the related object column is set to the value.
        if ($type === 'has_one') {
            $related_object->setRaw($foreign_column, $value);
            $this->relation_objects[$name] = false;
        }

        //has many, the columns on the entities in the related
        //collection are set to the value.
        if ($type === 'has_many') {
            $related_object->setColumnRaw($foreign_column, $value);
            $this->relation_objects[$name] = $foreign_class::newCollection();
        }

        return;
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
        foreach ($values as $key => $value) {
            $this->setRaw($key, $value);
        }

        return $this;
    }

    /**
     * Set an array of values, discarding any columns / relations in
     * the blacklist for this entity.
     *
     * The primary key is discarded if no blacklist if set.
     */
    public function setValuesSafe(array $values = [])
    {
        $blacklist = is_array(static::$blacklist) ? static::$blacklist : [static::$primary_key];

        foreach ($values as $key => $value) {
            if (!in_array($key, $blacklist) && isset(self::$column_cache[get_called_class()][$key])) {
                $this->set($key, $values[$key]);
            }
        }
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
     * Get whether this entity has been modified.
     *
     * @return bool True if modified, false if not
     */
    public function isModified()
    {
        return !empty($this->modified);
    }

    /**
     * Persist this entity to the database using an insert query.
     */
    public function insert()
    {
        if ($this->stored) {
            throw new \LogicException("You may not insert an already stored entity");
        }

        $this->callEvent('insert');

        if (empty($this->modified)) {
            return;
        }

        $values = array_intersect_key($this->values, $this->modified);
        $this->connection->insert(static::$table, $values, static::$types);
        //this will only work with some database vendors for now.
        $this->values[static::$primary_key] = $this->connection->lastInsertId();
        $this->setStored();
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
        $this->callEvent('update');

        if (empty($this->modified)) {
            return;
        }
        $values = array_intersect_key($this->values, $this->modified);
        $where = [static::$primary_key => $this->getPrimaryKey()];
        $this->connection->update(static::$table, $values, $where, static::$types);
        $this->setStored();
    }

    /**
     * Set whether this entity is stored in the database or not.
     *
     * @param bool $stored True if stored, false if not
     */
    public function setStored($stored = true)
    {
        $this->stored = (bool) $stored;
        $this->modified = $stored ? [] : $this->values;

        return $this;
    }

    /**
     * Get whether this entity is stored in the database or not.
     *
     * @return bool True if stored, false if not
     */
    public function isStored()
    {
        return $this->stored;
    }

    /**
     * Save this entity to the database, either with an insert or
     * update query.
     */
    public function save()
    {
        return $this->stored ? $this->update() : $this->insert();
    }

    /**
     * Delete this entity from the database.
     *
     * @return Entity This entity
     */
    public function delete()
    {
        $where = [static::$primary_key => $this->getPrimaryKey()];

        $this->connection->delete(static::$table, $where);
        $this->setStored(false);

        return $this;
    }

    /**
     * Delete all entities from the database.
     *
     * @param Connection $connection A connection instance
     */
    public static function deleteAll(Connection $connection)
    {
        //this feels like a hack, but works for now
        return $connection->delete(static::$table, [1 => 1], [\PDO::PARAM_INT]);
    }

    /**
     * Create a new EntityCollection class. Override this method in
     * child classes to use a custom collection class.
     *
     * @param array $entities An array of entities to add to the collection
     */
    public static function newCollection(array $entities = [])
    {
        return new EntityCollection($entities);
    }

    /**
     * Create a new collection with an amount of empty entities.
     *
     * @param Connection $connection A connection instance
     * @param int        $amount     The amount of new Entities to create
     */
    public static function create(Connection $connection, $amount = 0)
    {
        $entities = [];
        for ($i = 0; $i < (int) $amount; $i++) {
            $entities[] = new static($connection);
        }

        return static::newCollection($entities);
    }

    /**
     * Select all entities matching an SQL query, and return the
     * results as a collection.
     *
     * @param Connection $connection   A connection instance
     * @param string     $sql          The SQL query
     * @param array      $parameters   Any bound parameters required for the query
     * @param array      $field_mapping An array of result columns => entity fields
     *
     * @return EntityCollection A collection containing the selected entities
     */
    public static function selectSQL(Connection $connection, $sql, array $parameters = [], array $field_mapping = [])
    {
        $stmt = $connection->prepare($sql);
        $stmt->execute($parameters);
        $results = array();
        while ($result = $stmt->fetch()) {
            foreach ($field_mapping as $result_column => $entity_field) {
                if (!isset($result[$result_column])) {
                    continue;
                }
                $result[$entity_field] = $result[$result_column];
                unset($result[$result_column]);
            }

            foreach (static::$types as $column => $type) {
                if (isset($result[$column])) {
                    $result[$column] = $connection->convertToPHPValue($result[$column], $type);
                }
            }

            $obj = new static($connection, $result);
            $obj->setStored();
            $results[] = $obj;
        }
        $collection = static::newCollection();
        $collection->setEntities($results);

        return $collection;
    }

    /**
     * Select a single entity matching an SQL query. If more than one
     * row is matched by the query, only the first entity will be
     * returned.
     *
     * @param Connection $connection    A connection instance
     * @param string     $sql           The SQL query
     * @param array      $parameters    Any bound parameters required for the query
     * @param array      $field_mapping An array of result columns => entity fields
     *
     * @return Entity|null The selected Entity, or null if no entity was found
     */
    public static function selectOneSQL(Connection $connection, $sql, array $parameters = [], array $field_mapping = [])
    {
        $stmt = $connection->prepare($sql);
        $stmt->execute($parameters);
        $result = $stmt->fetch();
        if ($result) {
            foreach ($field_mapping as $result_column => $entity_field) {
                if (!isset($result[$result_column])) {
                    continue;
                }
                $result[$entity_field] = $result[$result_column];
                unset($result[$result_column]);
            }

            foreach (static::$types as $column => $type) {
                if (isset($result[$column])) {
                    $result[$column] = $connection->convertToPHPValue($result[$column], $type);
                }
            }
            $entity = new static($connection, $result);

            return $entity->setStored();
        }

        return null;
    }

    /**
     * Select entities using an EntitySelector instance.
     *
     * @param  Connection     $connection A connection instance
     * @return EntitySelector A selector instance
     */
    public static function select(Connection $connection)
    {
        return new EntitySelector(AbstractSelector::fromConnection($connection, static::$table, static::$types), get_called_class());
    }

    /**
     * Select a single entity using an EntitySelector instance.
     *
     * @param  Connection     $connection A connection instance
     * @return EntitySelector A selector instance
     */
    public static function selectOne(Connection $connection)
    {
        $selector = new EntitySelector(AbstractSelector::fromConnection($connection, static::$table, static::$types), get_called_class());

        return $selector->one();
    }

    /**
     * Select a single entity matching a primary key.
     *
     * @param  Connection  $connection  A connection instance
     * @param  mixed       $primary_key The primary key
     * @return Entity|null The entity, or null if not found
     */
    public static function selectPrimaryKey(Connection $connection, $primary_key)
    {
        return static::selectOne($connection)
            ->where(static::$primary_key, $primary_key)
            ->execute();
    }
}
