<?php

namespace ActiveDoctrine\Tests\Functional;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Schema\SchemaException;

/**
 * FunctionalTestCase
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class FunctionalTestCase extends \PHPUnit_Framework_TestCase
{

    protected static $connection;
    protected $loaded_schemas = [];

    public function tearDown()
    {
        $conn = $this->getConn();
        $current = $conn->getSchemaManager()->createSchema();
        $dropped = clone $current;

        foreach ($this->loaded_schemas as $schema) {
            $schema->down($dropped);
        }

        try {
            $queries = $current->getMigrateToSQL($dropped, $conn->getDatabasePlatform());
            foreach ($queries as $query) {
                $conn->executeQuery($query);
            }
        } catch (SchemaException $e) {
            echo $e->getMessage();
        }
    }

    public function getConn()
    {
        if (isset(static::$connection)) {
            return static::$connection;
        }
        if (isset(
            $_ENV['db_driver'],
            $_ENV['db_user'],
            $_ENV['db_password'],
            $_ENV['db_host'],
            $_ENV['db_name'],
            $_ENV['db_port']
        )) {
            $connect_params = [
                'driver' => $_ENV['db_driver'],
                'user' => $_ENV['db_user'],
                'password' => $_ENV['db_password'],
                'host' => $_ENV['db_host'],
                'dbname' => $_ENV['db_name'],
                'port' => $_ENV['db_port']
            ];
        } else {
            $connect_params = [
                'driver' => 'pdo_sqlite',
                'memory' => true
            ];
        }

        //set up a special logger that counts queries here
        $configuration = new Configuration();
        static::$connection = DriverManager::getConnection($connect_params, $configuration);

        return static::$connection;
    }

    protected function loadSchema($entity_group)
    {
        $conn = $this->getConn();
        $current = $conn->getSchemaManager()->createSchema();
        $new = clone $current;

        $schema_class = 'ActiveDoctrine\Tests\Fixtures\Schemas\\' . ucfirst($entity_group) . 'Schema';
        $schema = new $schema_class();

        try {
            $schema->up($new);

            $queries = $current->getMigrateToSQL($new, $conn->getDatabasePlatform());
            foreach ($queries as $query) {
                $conn->executeQuery($query);
            }
        } catch (SchemaException $e) {
            echo $e->getMessage();
        }

        $this->loaded_schemas[] = $schema;

        return $schema;
    }

    protected function loadData($entity_group)
    {
        $data_class = 'ActiveDoctrine\Tests\Fixtures\Data\\' . ucfirst($entity_group) . 'Data';
        $data = new $data_class();
        $data->loadData($this->getConn());

        return $data;
    }

}
