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
    protected $counting;
    protected $with = [];

    public function __construct(AbstractSelector $selector, $entity_class)
    {
        $this->entity_class = $entity_class;
        $this->selector = $selector;
        $this->connection = $selector->getConnection();
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

    /**
     * Return a single Entity instead of an EntityCollection when this
     * query is executed. A limit of 1 will be applied to the
     * underlying SQL query. If the limit on the query is modified and
     * it returns more than one result, the first will be returned.
     */
    public function one()
    {
        $this->limit(1);
        $this->single = true;

        return $this;
    }

    /**
     * Turn this query into a 'count' query, returning the number of
     * rows instead of entities.
     */
    public function count()
    {
        $this->counting = true;
        $this->selector->count();

        return $this;
    }

    /**
     * Eagerly load related entities with this query. A new
     * EntitySelector instance will be created which can be configured
     * with a supplied callback (including loading relations of those
     * entities).
     *
     * @param string       $relation The name of the relation
     * @param Closure|null $callback An optional callback for the resulting EntitySelector
     */
    public function with($relation, \Closure $callback = null)
    {
        if ($callback === null) {
            $callback = function () {};
        }

        $this->with[$relation] = $callback;

        return $this;
    }

    /**
     * Execute the query and return the result.
     *
     * @return mixed An EntityCollection, Entity or integer.
     */
    public function execute()
    {
        if ($this->counting) {
            return $this->executeCount();
        }

        if ($this->single) {
            return $this->executeSingle($this->entity_class);
        }

        return $this->executeCollection($this->entity_class);
    }

    protected function executeCount()
    {
        $stmt = $this->connection->prepare($this->selector->getSQL());
        $stmt->execute($this->selector->getParams());

        return (int) $stmt->fetchColumn();
    }

    protected function executeSingle($entity_class)
    {
        $result =  $entity_class::selectOneSQL($this->connection, $this->selector->getSQL(), $this->selector->getParams());

        //exit early if there are no relations requested or no result
        if (empty($this->with) || $result === null) {
            return $result;
        }

        //fetch relations that have been specified
        foreach ($this->with as $name => $callback) {
            //create a new selector for the relation
            list($type, $foreign_class, $foreign_column, $column) = $entity_class::getRelationDefinition($name);
            switch ($type) {
            case 'has_one':
                $selector = $foreign_class::selectOne($this->connection)
                    ->where($foreign_column, '=', $result->getRaw($column));
                break;
            case 'belongs_to':
                $selector = $foreign_class::selectOne($this->connection)
                    ->where($foreign_column, '=', $result->getRaw($column));
                break;
            case 'has_many':
                $selector = $foreign_class::select($this->connection)
                    ->where($foreign_column, '=', $result->getRaw($column));
                break;
            default:
                //throw exception for invalid relation
            }
            //configure the selector with the supplied callback
            $callback($selector);
            //execute
            $related_object = $selector->execute();
            //set the relation
            $result->setRelation($name, $related_object);
        }

        return $result;
    }

    public function where($column, $expression = null, $value = null)
    {
        if ($expression instanceof Entity || $value instanceof Entity) {
            $entity_class = $this->entity_class;
            $relation = $entity_class::getRelationDefinition($column);
            $column = $relation[3];
            $relation_column = $relation[2];
            if ($expression instanceof Entity) {
                $expression = $expression->getRaw($relation_column);
            } else {
                $value = $value->getRaw($relation_column);
            }
        }

        $this->selector->where($column, $expression, $value);

        return $this;
    }

    protected function executeCollection($entity_class)
    {
        $collection = $entity_class::selectSQL($this->connection, $this->selector->getSQL(), $this->selector->getParams());

        //exit early if there are no relations requested or no result
        if (empty($this->with) || count($collection) === 0) {
            return $collection;
        }

        //fetch relations that have been specified
        foreach ($this->with as $name => $callback) {
            $relation = $entity_class::getRelationDefinition($name);
            list($type, $foreign_class, $foreign_column, $column) = $relation;

            //create a new selector for the relation using the values
            //of the joining column. Unique values prevents duplicate
            //entities
            $values = array_unique($collection->getColumn($column));
            $selector = $foreign_class::select($this->connection)
                ->whereIn($foreign_column, $values);

            //configure the selector with the supplied callback
            $callback($selector);

            //fetch the related entities
            $foreign_collection = $selector->execute();

            //match up the entities from the query with the current
            //entities
            switch ($type) {
            case 'has_one':
                $this->hydrateHasOne($name, $relation, $collection, $foreign_collection);
                break;
            case 'belongs_to':
                $this->hydrateHasOne($name, $relation, $collection, $foreign_collection);
                break;
            case 'has_many':
                $this->hydrateHasMany($name, $relation, $collection, $foreign_collection);
                break;
            default:
                //throw exception for invalid relation
            }

            //finally, set the foreign collection as a relation of
            //this collection
        }

        return $collection;
    }

    protected function hydrateHasOne($relation_name, array $relation, EntityCollection $collection, EntityCollection $foreign_collection)
    {
        list($type, $foreign_class, $foreign_column, $column) = $relation;
        //create an indexed list of related entities, indexed
        //by the key joining them.
        $indexed = [];
        foreach ($foreign_collection as $foreign_entity) {
            $index = $foreign_entity->getRaw($foreign_column);
            $indexed[$index] = $foreign_entity;
        }

        //loop through the original collection and set the
        //relation if it exists in the indexed result set
        foreach ($collection as $entity) {
            $index = $entity->getRaw($column);
            if (isset($indexed[$index])) {
                $entity->setRelation($relation_name, $indexed[$index]);
            } else {
                //the relation doesn't exist, but we need to notify the
                //entity of that so it won't attempt to fetch
                //it.
                $entity->setRelation($relation_name, null);
            }
        }
    }

    protected function hydrateHasMany($relation_name, array $relation, EntityCollection $collection, EntityCollection $foreign_collection)
    {
        list($type, $foreign_class, $foreign_column, $column) = $relation;
        //create an indexed list of related entities, indexed
        //by the key joining them. Each index can have
        //more than one entity and is represented as an
        //array.
        $indexed = [];
        foreach ($foreign_collection as $foreign_entity) {
            $index = $foreign_entity->getRaw($foreign_column);
            $indexed[$index][] = $foreign_entity;
        }

        //loop through the original collection and set the
        //relation if it exists in the indexed result set
        foreach ($collection as $entity) {
            $index = $entity->getRaw($column);
            if (isset($indexed[$index])) {
                $foreign_child_collection = $foreign_class::newCollection($indexed[$index]);
                $entity->setRelation($relation_name, $foreign_child_collection);
            } else {
                //the relation doesn't exist, so give the entity a blank collection
                $entity->setRelation($relation_name, $foreign_class::newCollection());
            }
        }
    }

}
