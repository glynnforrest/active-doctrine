<?php

namespace ActiveDoctrine\Tests\Functional;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Logging\DebugStack;

/**
 * FunctionalTestCase
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class FunctionalTestCase extends \PHPUnit_Framework_TestCase
{

    protected static $connection;
    protected static $logger;
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

    protected function getSQLLogger()
    {
        if (!isset(static::$logger)) {
            static::$logger = new DebugStack();
        }

        return static::$logger;
    }

    protected function resetQueryCount()
    {
        $this->getSQLLogger()->queries = [];
    }

    protected function getQueryCount()
    {
        return count($this->getSQLLogger()->queries);
    }

    public function getConn()
    {
        if (isset(static::$connection)) {
            return static::$connection;
        }

        //set up a special logger that counts queries here
        $configuration = new Configuration();
        $configuration->setSQLLogger($this->getSQLLogger());
        static::$connection = DriverManager::getConnection($this->getConnectionParams(), $configuration);

        return static::$connection;
    }

    protected function getConnectionParams()
    {
        if (isset(
            $_ENV['db_driver'],
            $_ENV['db_user'],
            $_ENV['db_password'],
            $_ENV['db_host'],
            $_ENV['db_name'],
            $_ENV['db_port']
        )) {
            return [
                'driver' => $_ENV['db_driver'],
                'user' => $_ENV['db_user'],
                'password' => $_ENV['db_password'],
                'host' => $_ENV['db_host'],
                'dbname' => $_ENV['db_name'],
                'port' => $_ENV['db_port'],
            ];
        }

        if (isset($_ENV['db_driver']) && $_ENV['db_driver'] === 'pdo_sqlite' && !isset($_ENV['db_memory'])) {
            return [
                'driver' => 'pdo_sqlite',
                'path' => isset($_ENV['db_path']) ? $_ENV['db_path'] : 'active_doctrine_tests.db3',
            ];
        }

        return [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];
    }

    /**
     * Normalise a name for instantiating schema and data classes.
     *
     * music_festival => MusicFestival
     *
     * @param string $string
     * @return string
     */
    protected function normalize($string)
    {
        return str_replace(' ', '', ucwords(preg_replace('/(_|-)+/', ' ', $string)));
    }

    protected function loadSchema($entity_group)
    {
        $conn = $this->getConn();
        $current = $conn->getSchemaManager()->createSchema();
        $new = clone $current;

        $entity_group = $this->normalize($entity_group);
        $schema_class = sprintf('ActiveDoctrine\Tests\Fixtures\%s\%sSchema', $entity_group, $entity_group);
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
        $entity_group = $this->normalize($entity_group);
        $data_class = sprintf('ActiveDoctrine\Tests\Fixtures\%s\%sData', $entity_group, $entity_group);
        $data = new $data_class();
        $data->loadData($this->getConn());

        return $data;
    }

}
