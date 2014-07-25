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
        $query = 'SELECT * FROM `' . $this->table . '`';
        if ($this->where) {
            $this->addWhere($query);
        }
        if ($this->order_by) {
            $this->addOrderBy($query);
        }
        if ($this->limit) {
            $query .= ' LIMIT ' . $this->limit;
            if ($this->offset) {
                $query .= ' OFFSET ' . $this->offset;
            }
        }

        return $query;
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

        //for the first where, there is no AND / OR logic
        $where = $this->where[0];
        $query .= sprintf(' WHERE `%s` %s ?', $where[0], $where[1]);
        $this->addParam($where[2]);

        $count = count($this->where);
        for ($i = 1; $i < $count; $i++) {
            $where = $this->where[$i];
            switch ($where[3]) {
            case self::AND_WHERE:
                $query .= sprintf(' AND `%s` %s ?', $where[0], $where[1]);
                break;
            case self::OR_WHERE:
                $query .= sprintf(' OR `%s` %s ?', $where[0], $where[1]);
                break;
            default:
                throw new \InvalidArgumentException('Unknown where clause type "%s"', $where[3]);
            }

            $this->addParam($where[2]);
        }
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
