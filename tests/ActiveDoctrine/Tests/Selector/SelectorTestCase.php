<?php

namespace ActiveDoctrine\Tests\Selector;

use Symfony\Component\Yaml\Yaml;

/**
 * SelectorTestCase
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class SelectorTestCase extends \PHPUnit_Framework_TestCase
{

    protected $yaml;
    protected $yaml_key;

    public function setUp()
    {
        if (!$this->yaml) {
            $this->yaml = Yaml::parse(file_get_contents(__DIR__ . '/sql.yml'));
        }
        if (!$this->yaml_key) {
            //get basename of class
            $key = basename(str_replace('\\', '/', get_called_class()));
            //strip SelectorTest - 12 chars - and lowercase
            $this->yaml_key = strtolower(substr($key, 0, -12));
        }
    }

    abstract protected function getSelector();

    protected function getYaml($name)
    {
        return $this->yaml[$name][$this->yaml_key];
    }

    protected function getYamlParams($name)
    {
        return $this->yaml['params_' . $name];
    }

    public function testSimple()
    {
        $s = $this->getSelector();
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
    }

    public function testWhereEquals()
    {
        $s = $this->getSelector()
                  ->where('id', '=', 1);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereLessThan()
    {
        $s = $this->getSelector()
                  ->where('id', '<', 4);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereEqualsAndLessThan()
    {
        $s = $this->getSelector()
                  ->where('id', '=', 4)
                  ->andWhere('index', '<', 10);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereMoreThanOrLessThan()
    {
        $s = $this->getSelector()
                  ->where('id', '>', 100)
                ->orWhere('id', '<', 50);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testLimit()
    {
        $s = $this->getSelector()
                  ->limit(10);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
    }

    public function testLimitOffset()
    {
        $s = $this->getSelector()
                  ->limit(10)
                  ->offset(20);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
    }

    public function testOrderBy()
    {
        $s = $this->getSelector()
                  ->orderBy('name');
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
    }

    public function testOrderByAsc()
    {
        $s = $this->getSelector()
                  ->orderBy('name', 'ASC');
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
    }

    public function testOrderByAscDesc()
    {
        $s = $this->getSelector()
                  ->orderBy('name', 'ASC')
                  ->orderBy('id', 'DESC');
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
    }

    public function testMixed1()
    {
        $s = $this->getSelector()
                  ->where('id', '<', 200)
                  ->andWhere('id', '>', 100)
                  ->orderBy('name', 'DESC')
                  ->orderBy('id')
                  ->limit(20);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereIn()
    {
        $s = $this->getSelector()
                  ->whereIn('id', [1, 2, 3, 4, 5, 20]);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereInAndWhereIn()
    {
        $s = $this->getSelector()
                  ->whereIn('id', [1, 2, 3])
                  ->andWhereIn('name', ['foo', 'bar', 'baz']);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereInOrWhereIn()
    {
        $s = $this->getSelector()
                  ->whereIn('id', [1, 2, 3])
                  ->orWhereIn('name', ['foo', 'bar', 'baz']);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereAndWhereIn()
    {
        $s = $this->getSelector()
                  ->where('name', '=', 'bar')
                  ->andWhereIn('id', [1, 2, 3]);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testCount()
    {
        $s = $this->getSelector()
            ->count();
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
    }

    public function testCountWhere()
    {
        $s = $this->getSelector()
            ->whereIn('name', ['foo', 'bar', 'baz'])
            ->count()
            ->orWhere('id', '>', 100);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

}
