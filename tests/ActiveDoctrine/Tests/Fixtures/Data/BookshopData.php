<?php

namespace ActiveDoctrine\Tests\Fixtures\Data;

use Doctrine\DBAL\Connection;

/**
 * BookshopData
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class BookshopData implements DataInterface
{

    public function loadData(Connection $connection)
    {
        $records = [
            ['Book 1', 'The very first book', 1],
            ['Book 2', 'The second book', 2],
        ];

        //pad to 50
        $count = count($records);
        for ($i = $count + 1; $i < 51; $i++) {
            $records[] = ["Book $i", "Book $i description", 3];
        }

        foreach ($records as $r) {
            $connection->insert('books', [
                'name' => $r[0],
                'description' => $r[1],
                'authors_id' => $r[2]
            ]);
        }
    }

}
