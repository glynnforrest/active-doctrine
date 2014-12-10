<?php

namespace ActiveDoctrine\Tests\Fixtures;

use Doctrine\DBAL\Connection;

/**
 * DataInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface DataInterface
{

    public function loadData(Connection $connection);

}
