<?php

namespace ActiveDoctrine\Selector;

/**
 * GenericSelector
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class GenericSelector extends AbstractSelector
{
    protected $begin_group;

    public function getSQL()
    {
        $query = sprintf(' FROM %s', $this->quoteIdentifier($this->table));
        if ($this->where) {
            $this->addWhere($query);
        }
        if ($this->order_by) {
            $this->addOrderBy($query);
        }
        if ($this->limit) {
            $query .= sprintf(' LIMIT %s', (int) $this->limit);
            if ($this->offset) {
                $query .= sprintf(' OFFSET %s', (int) $this->offset);
            }
        }

        //prepend the select after constructing the rest of the query
        //in case we need to wrap it in a count
        if ($this->counting) {
            if ($this->limit) {
                //a subselect is needed if there is a limit, otherwise the
                //limit will be applied to the count result - 1 row.
                return 'SELECT COUNT(1) FROM (SELECT *' . $query . ') t';
            }

            return 'SELECT COUNT(1)' . $query;
        }

        return 'SELECT *' . $query;
    }

    protected function addWhere(&$query)
    {
        /**
         * $this->where is of the form
         * [
         *     [$column, $expression, $value, $type],
         *     //etc
         * ]
         * e.g. ['id', '=', 1, 'self::AND_WHERE']
         */

        $this->begin_group = true;
        $query .= ' WHERE ';

        $count = count($this->where);
        for ($i = 0; $i < $count; $i++) {
            $where = $this->where[$i];
            $this->maybePrependWhere($query, $where);

            if ($where[3] === self::AND_WHERE || $where[3] === self::OR_WHERE) {
                $query .= sprintf('%s %s ?', $this->quoteIdentifier($where[0]), $where[1]);
                $this->addParam($where[0], $where[2]);
                continue;
            }
            if ($where[3] === self::AND_WHERE_IN || $where[3] === self::OR_WHERE_IN) {
                $this->addWhereInSegment($query, $where);
                continue;
            }
            if ($where[3] === self::BEGIN_GROUP_AND || $where[3] === self::BEGIN_GROUP_OR) {
                $query .= '(';
                $this->begin_group = true;
                continue;
            }

            // self::END_GROUP
            $query .= ')';
        }
    }

    protected function maybePrependWhere(&$query, array $where)
    {
        if ($this->begin_group) {
            $this->begin_group = false;

            return;
        }
        if ($where[3] === self::AND_WHERE || $where[3] === self::AND_WHERE_IN || $where[3] === self::BEGIN_GROUP_AND) {
            $query .= ' AND ';

            return;
        }
        if ($where[3] === self::OR_WHERE || $where[3] === self::OR_WHERE_IN || $where[3] === self::BEGIN_GROUP_OR) {
            $query .= ' OR ';

            return;
        }
    }

    protected function addWhereInSegment(&$query, array $where)
    {
        $query .= sprintf('%s IN (%s)', $this->quoteIdentifier($where[0]), substr(str_repeat('?, ', count($where[1])), 0, -2));
        $this->addParam($where[0], $where[1]);
    }

    protected function addOrderBy(&$query)
    {
        $sql = '';
        foreach ($this->order_by as $column => $sort) {
            $sql .= sprintf(', %s %s', $this->quoteIdentifier($column), $sort);
        }
        $query .= ' ORDER BY ' . substr($sql, 2);
    }
}
