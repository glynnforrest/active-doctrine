<?php

namespace ActiveDoctrine\Tests\Fixtures\Nodes;

use ActiveDoctrine\Entity\Entity;

/**
 * Node
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Node extends Entity
{
    protected static $fields = [
        'id',
        'parent_id',
        'value',
    ];
    protected static $table = 'nodes';
    protected static $relations = [
        'parent' => [
            'belongs_to', 'ActiveDoctrine\Tests\Fixtures\Nodes\Node', 'id', 'parent_id'
        ],
        'children' => [
            'has_many', 'ActiveDoctrine\Tests\Fixtures\Nodes\Node', 'parent_id' ,'id'
        ]
    ];
}
