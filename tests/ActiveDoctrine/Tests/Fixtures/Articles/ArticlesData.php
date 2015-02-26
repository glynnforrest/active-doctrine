<?php

namespace ActiveDoctrine\Tests\Fixtures\Articles;

use Doctrine\DBAL\Connection;
use ActiveDoctrine\Tests\Fixtures\DataInterface;

/**
 * ArticlesData
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ArticlesData implements DataInterface
{
    public function loadData(Connection $connection)
    {
        $records = [];

        for ($i = 1; $i < 21; $i++) {
            $records[] = ["Article $i"];
        }

        foreach ($records as $r) {
            $connection->insert('articles', [
                'title' => $r[0],
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ],
            [
                'created_at' => 'datetime',
                'updated_at' => 'datetime',
            ]);
        }
    }
}
