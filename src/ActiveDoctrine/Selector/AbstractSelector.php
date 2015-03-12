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
    const END_GROUP = 5;
    const BEGIN_GROUP_OR = 6;
    const BEGIN_GROUP_AND = 7;

    protected $connection;
    protected $table;
    protected $types = [];
    protected $params = [];
    protected $param_columns = [];

    protected $where = [];
    protected $order_by = [];
    protected $limit;
    protected $offset;
    protected $counting;

    protected $quote_char = '"';

    public function __construct(Connection $connection = null, $table, array $types = [])
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->types = $types;
    }

    /**
     * Get the Connection.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Quote an identifier so it can be used as a table or column
     * name. Composite identifiers (table.column) are not supported
     * because selectors operate on one table only.
     *
     * @see Doctrine\DBAL\Platforms\AbstractPlatform#quoteSingleIdentifier() It has been added here so selectors don't rely on Connection or Platform instances.
     *
     * @param string $identifier The identifier to quote
     *
     * @return string The quoted identifier
     */
    protected function quoteIdentifier($identifier)
    {
        $c = $this->quote_char;

        return $c . str_replace($c, $c.$c, $identifier) . $c;
    }

    /**
     * Get this selector as an SQL query.
     *
     * @return string The SQL query
     */
    abstract public function getSQL();

    /**
     * Get the parameters to be used in the prepared query. No type
     * conversion will take place.
     *
     * @return array The parameters
     */
    public function getParamsRaw()
    {
        return $this->params;
    }

    /**
     * Get the parameters to be used in the prepared query, converted
     * to the correct database type.
     *
     * @return array The parameters
     */
    public function getParams()
    {
        //exit early if no types are set
        if (empty($this->types)) {
            return $this->params;
        }

        $params = [];
        $num_params = count($this->params);
        for ($i = 0; $i < $num_params; $i++) {
            $value = $this->params[$i];
            $column = $this->param_columns[$i];

            if (isset($this->types[$column])) {
                $params[] = $this->connection->convertToDatabaseValue($value, $this->types[$column]);
                continue;
            }
            $params[] = $value;
        }

        return $params;
    }

    /**
     * Prepare the current SQL query.
     *
     * @return \Doctrine\DBAL\Driver\Statement The prepared statement
     */
    public function prepare()
    {
        return $this->connection->prepare($this->getSQL());
    }

    /**
     * Prepare and execute the current SQL query, returning an array
     * of the results.
     *
     * @return array The results
     */
    public function execute()
    {
        $stmt = $this->connection->prepare($this->getSQL());
        $stmt->execute($this->getParams());

        return $this->counting ? (int) $stmt->fetchColumn() : $stmt->fetchAll();
    }

    /**
     * Get a vendor-specific selector based on a connection instance.
     *
     * @param  Connection       $connection A connection instance
     * @param  string           $table      The table to select from
     * @return AbstractSelector A selector instance
     * @throws DBALException
     */
    public static function fromConnection(Connection $connection, $table, array $types = [])
    {
        $name = $connection->getDriver()->getName();
        switch ($name) {
        case 'pdo_mysql':
            return new MysqlSelector($connection, $table, $types);
        case 'pdo_sqlite':
            return new SqliteSelector($connection, $table, $types);
        default:
            throw new DBALException("Unsupported database type: $name");
        }
    }

    /**
     * Add a param to be executed in the query. To ensure the order of
     * parameters, this should be called during the buildSQL method.
     *
     * @param string $column
     * @param mixed $value
     */
    protected function addParam($column, $value)
    {
        if (is_array($value)) {
            $this->params = array_merge($this->params, $value);
            $this->param_columns = array_merge($this->param_columns, array_fill(0, count($value), $column));
            return;
        }
        $this->params[] = $value;
        $this->param_columns[] = $column;
    }

    protected function doWhere($type, $column, $expression = null, $value = null)
    {
        if ($column instanceof \Closure) {
            $this->where[] = [$type === self::OR_WHERE ? self::BEGIN_GROUP_OR : self::BEGIN_GROUP_AND];
            $column($this);
            $this->where[] = [self::END_GROUP];

            return $this;
        }

        if (!$expression) {
            throw new \InvalidArgumentException('A where clause not containing a closure must have at least 2 arguments.');
        }

        //assume an equality if only two arguments are provided
        if ($value === null) {
            $value = $expression;
            $expression = '=';
        }

        $this->where[] = [$type, $column, $expression, $value];

        return $this;
    }

    /**
     * Add a 'where' clause to the query.
     *
     * @param string $column     The column name
     * @param string $expression The comparison, e.g. '=' or '<'
     * @param string $value      The value
     */
    public function where($column, $expression = null, $value = null)
    {
        return $this->doWhere(self::AND_WHERE, $column, $expression, $value);
    }

    /**
     * Add an 'and where' clause to the query.
     *
     * @param string $column     The column name
     * @param string $expression The comparison, e.g. '=' or '<'
     * @param string $value      The value
     */
    public function andWhere($column, $expression = null, $value = null)
    {
        return $this->doWhere(self::AND_WHERE, $column, $expression, $value);
    }

    /**
     * Add an 'or where' clause to the query.
     *
     * @param string $column     The column name
     * @param string $expression The comparison, e.g. '=' or '<'
     * @param string $value      The value
     */
    public function orWhere($column, $expression = null, $value = null)
    {
        return $this->doWhere(self::OR_WHERE, $column, $expression, $value);
    }

    /**
     * Add a 'where in' clause to the query.
     *
     * @param string $column The column name
     * @param array  $values A list of values to query with
     */
    public function whereIn($column, array $values)
    {
        $this->where[] = [self::AND_WHERE_IN, $column, $values];

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
        $this->where[] = [self::AND_WHERE_IN, $column, $values];

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
        $this->where[] = [self::OR_WHERE_IN, $column, $values];

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
        $this->order_by[$column] = $sort;

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

    /**
     * Turn this query into a 'count' query, returning the number of
     * rows in the database instead of the results.
     */
    public function count()
    {
        $this->counting = true;

        return $this;
    }

}
