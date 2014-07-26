<?php

namespace ActiveDoctrine\Selector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

/**
 * Selectors implement an extremely minimal subset of an SQL builder,
 * designed solely for selecting entities. There are some conscious
 * limitations that help to keep this implementation simple:
 *
 * A query can only operate on one table. Selecting entities from
 * other tables is covered by using another selector instance.
 *
 * As there is only one table, no table alias is required.
 *
 * By covering only a small subset of queries, all values can be
 * prepared automatically to guard against injection attacks.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class AbstractSelector
{

    const AND_WHERE = 1;
    const OR_WHERE = 2;
    const AND_WHERE_IN = 3;
    const OR_WHERE_IN = 4;

    protected $query = array();
    protected $params = array();
    protected $table;

    protected $where = [];
    protected $order_by = [];
    protected $limit;
    protected $offset;

    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Get this selector as an SQL query.
     *
     * @return string The SQL query
     */
    abstract public function getSQL();

    /**
     * Get the parameters to be used in the prepared query.
     *
     * @return array The parameters
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get a vendor-specific selector based on a connection instance.
     *
     * @param  Connection       $connection A connection instance
     * @param  string           $table      The table to select from
     * @return AbstractSelector A selector instance
     * @throws DBALException
     */
    public static function fromConnection(Connection $connection, $table)
    {
        $name = $connection->getDriver()->getName();
        switch ($name) {
        case 'pdo_mysql':
            return new MysqlSelector($table);
        default:
            throw new DBALException("Unsupported database type: $name");
        }
    }

    /**
     * Add a param to be executed in the query. To ensure the order of
     * parameters, this should be called during the buildSQL method.
     *
     * @param mixed $value
     */
    protected function addParam($value)
    {
        if (is_array($value)) {
            $this->params = array_merge($this->params, $value);

            return;
        }

        $this->params[] = $value;
    }

    /**
     * Add a 'where' clause to the query.
     *
     * @param string $column     The column name
     * @param string $expression The comparison, e.g. '=' or '<'
     * @param string $value      The value
     */
    public function where($column, $expression, $value)
    {
        $this->where[] = [$column, $expression, $value, self::AND_WHERE];

        return $this;
    }

    /**
     * Add an 'and where' clause to the query.
     *
     * @param string $column     The column name
     * @param string $expression The comparison, e.g. '=' or '<'
     * @param string $value      The value
     */
    public function andWhere($column, $expression, $value)
    {
        $this->where[] = [$column, $expression, $value, self::AND_WHERE];

        return $this;
    }

    /**
     * Add an 'or where' clause to the query.
     *
     * @param string $column     The column name
     * @param string $expression The comparison, e.g. '=' or '<'
     * @param string $value      The value
     */
    public function orWhere($column, $expression, $value)
    {
        $this->where[] = [$column, $expression, $value, self::OR_WHERE];

        return $this;
    }

    /**
     * Add a 'where in' clause to the query.
     *
     * @param string $column The column name
     * @param array  $values A list of values to query with
     */
    public function whereIn($column, array $values)
    {
        $this->where[] = [$column, $values, null, self::AND_WHERE_IN];

        return $this;
    }

    /**
     * Add an 'and where in' clause to the query.
     *
     * @param string $column The column name
     * @param array  $values A list of values to query with
     */
    public function andWhereIn($column, array $values)
    {
        $this->where[] = [$column, $values, null, self::AND_WHERE_IN];

        return $this;
    }

    /**
     * Add an 'or where in' clause to the query.
     *
     * @param string $column The column name
     * @param array  $values A list of values to query with
     */
    public function orWhereIn($column, array $values)
    {
        $this->where[] = [$column, $values, null, self::OR_WHERE_IN];

        return $this;
    }

    /**
     * Add an 'order by' clause to the query.
     *
     * @param string $column The column name
     * @param string $sort   The sort order, either 'ASC' or 'DESC'
     */
    public function orderBy($column, $sort = 'ASC')
    {
        $sort = strtoupper($sort);
        if ($sort !== 'DESC') {
            $sort = 'ASC';
        }
        $this->order_by[] = [$column, $sort];

        return $this;
    }

    /**
     * Add a 'limit' clause to the query.
     *
     * @param int $limit The amount of results to limit the query to
     */
    public function limit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * Add an 'offset' clause to the query.
     *
     * @param int $offset The amount to offset the results by
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;

        return $this;
    }

}
