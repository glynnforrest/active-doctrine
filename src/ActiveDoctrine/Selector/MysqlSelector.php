<?php

namespace ActiveDoctrine\Selector;

/**
 * MysqlSelector
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MysqlSelector extends AbstractSelector
{

    public function getSQL()
    {
        $query = sprintf(' FROM `%s`', $this->table);
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

        $query .= ' WHERE ';

        //for the first where, there is no AND / OR logic. Just make
        //the distinction between an ordinary where and a where in
        $where = $this->where[0];
        if ($where[3] === self::AND_WHERE_IN || $where[3] === self::OR_WHERE_IN) {
            $this->addWhereInSegment($query, $where);
        } else {
            $query .= sprintf('`%s` %s ?', $where[0], $where[1]);
            $this->addParam($where[2]);
        }

        //loop through the remaining where segments, adding AND/OR
        //plus the segment.
        $count = count($this->where);
        for ($i = 1; $i < $count; $i++) {
            $where = $this->where[$i];
            switch ($where[3]) {
            case self::AND_WHERE:
                $query .= sprintf(' AND `%s` %s ?', $where[0], $where[1]);
                $this->addParam($where[2]);
                break;
            case self::OR_WHERE:
                $query .= sprintf(' OR `%s` %s ?', $where[0], $where[1]);
                $this->addParam($where[2]);
                break;
            case self::AND_WHERE_IN:
                $query .= ' AND ';
                $this->addWhereInSegment($query, $where);
                break;
            case self::OR_WHERE_IN:
                $query .= ' OR ';
                $this->addWhereInSegment($query, $where);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown where clause type "%s"', $where[3]));
            }
        }
    }

    protected function addWhereInSegment(&$query, $where)
    {
        $query .= sprintf('`%s` IN (%s)', $where[0], substr(str_repeat('?, ', count($where[1])), 0, -2));
        $this->addParam($where[1]);
    }

    protected function addOrderBy(&$query)
    {
        $order = $this->order_by[0];
        $query .= sprintf(' ORDER BY `%s` %s', $order[0], $order[1]);
        $count = count($this->order_by);
        for ($i = 1; $i < $count; $i++) {
            $order = $this->order_by[$i];
            $query .= sprintf(', `%s` %s', $order[0], $order[1]);
        }
    }

}
