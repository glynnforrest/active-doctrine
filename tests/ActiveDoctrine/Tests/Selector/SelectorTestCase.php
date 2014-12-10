<?php

namespace ActiveDoctrine\Tests\Selector;

use Symfony\Component\Yaml\Yaml;
use Doctrine\DBAL\Connection;

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

    abstract protected function getSelectorWithMock(Connection $connection);

    protected function getYaml($name)
    {
        return $this->yaml[$name][$this->yaml_key];
    }

    protected function getYamlParams($name)
    {
        return $this->yaml['params_' . $name];
    }

    protected function getPlatformYamlParams($name)
    {
        return $this->yaml['params_' . $name][$this->yaml_key];
    }

    public function testGetConnection()
    {
        $this->assertInstanceOf('Doctrine\DBAL\Connection', $this->getSelector()->getConnection());
    }

    public function testSimple()
    {
        $s = $this->getSelector();
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
    }

    public function whereMethodsProvider()
    {
        return [
            ['where'],
            ['orWhere'],
            ['andWhere'],
        ];
    }

    /**
     * @dataProvider whereMethodsProvider
     */
    public function testWhereEquals($method)
    {
        $s = $this->getSelector()
                  ->$method('id', '=', 1);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    /**
     * @dataProvider whereMethodsProvider
     */
    public function testWhereEqualsShorthand($method)
    {
        $s = $this->getSelector()
                  ->$method('id', 1);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    /**
     * @dataProvider whereMethodsProvider
     */
    public function testWhereNotEnoughArguments($method)
    {
        $msg = 'A where clause not containing a closure must have at least 2 arguments.';
        $this->setExpectedException('\InvalidArgumentException', $msg);
        $s = $this->getSelector()
                  ->$method('id');
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

    /**
     * @dataProvider whereMethodsProvider
     */
    public function testWhereGrouping($method)
    {
        $s = $this->getSelector()
                  ->$method(function ($s) {
                      $s->where('id', '>', 20)
                        ->orWhere('id', '<', 10);
                  })
                  ->andWhere('status', '=', 1);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    /**
     * @dataProvider whereMethodsProvider
     */
    public function testWhereGroupingOr($method)
    {
        $s = $this->getSelector()
                  ->$method('id', '>', 100)
                  ->orWhere(function ($s) use ($method) {
                      $s->$method('id', '<', 50)
                        ->andWhere('status', '=', 1);
                  });
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    /**
     * @dataProvider whereMethodsProvider
     */
    public function testWhereGroupingAnd($method)
    {
        $s = $this->getSelector()
                  ->$method('id', '>', 100)
                  ->andWhere(function ($s) use ($method) {
                      $s->$method('status', '>', 4)
                        ->orWhere('status', '=', 1);
                  });
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    /**
     * @dataProvider whereMethodsProvider
     */
    public function testWhereGroupingNested($method)
    {
        $s = $this->getSelector()
                  ->$method('id', '>', 100)
                  ->andWhere(function ($s) use ($method) {
                          $s->$method('status', '>', 50)
                          ->orWhere(function($s) {
                              $s->where('status', '=', 4)
                                ->andWhere('id', '=', 200);
                                  });
                      });
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    /**
     * @dataProvider whereMethodsProvider
     */
    public function testWhereGroupingNestedHeadFirst($method)
    {
        $s = $this->getSelector()
                  ->$method('id', '>', 100)
                  ->andWhere(function ($s) use ($method) {
                          $s->$method(function($s) {
                              $s->where('status', '=', 4)
                                ->andWhere('id', '=', 200);
                                  })
                          ->orWhere('status', '>', 50);
                      });
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    /**
     * @dataProvider whereMethodsProvider
     */
    public function testWhereGroupingWhereIn($method)
    {
        $s = $this->getSelector()
                  ->$method('id', '>', 100)
                  ->andWhere(function ($s) use ($method) {
                          $s->$method(function($s) {
                              $s->whereIn('status', [4, 5, 7])
                                ->andWhere('id', '=', 200);
                                  })
                          ->orWhere('status', '>', 50);
                      });
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testSillyNesting()
    {
        $s = $this->getSelector()
                  ->where(function($s) {
                      $s->where(function($s) {
                          $s->where(function($s) {
                              $s->where(function($s) {
                                  $s->where(function($s) {
                                    $s->where('id', '=', 4)
                                      ->andWhere('id', '=', 4);
                                  })
                                    ->andWhere('id', '=', 4);
                              })
                                ->andWhere('id', '=', 4);
                          })
                            ->andWhere('id', '=', 4);
                      })
                        ->andWhere('id', '=', 4);
                  })
                  ->andWhere('id', '=', 4);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereNestedShorthandEquals()
    {
        $s = $this->getSelector()
                  ->where('id', 1)
                  ->orWhere('foo', 'bar')
                  ->andWhere(function($s) {
                      $s->where('bar', 'baz')
                        ->orWhere('foo', 'baz');
                  });
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

    public function testOrderBySameColumnTwice()
    {
        $s = $this->getSelector()
                  ->orderBy('id', 'ASC')
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

    public function testCountWhereLimit()
    {
        $s = $this->getSelector()
            ->whereIn('name', ['foo', 'bar', 'baz'])
            ->orWhere('id', '>', 100)
            ->count()
            ->limit(100);
        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($this->getYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereTypeConversion()
    {
        $date = new \DateTime('Jan 1st 2000');
        $types = ['date' => 'date'];

        $s = $this->getSelector($types)
            ->where('date', '>', $date);

        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame([$date], $s->getParamsRaw());
        $this->assertSame($this->getPlatformYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testWhereInTypeConversion()
    {
        $dates = [
            new \DateTime('Jan 1st 2000'),
            new \DateTime('Feb 2nd 2001'),
            new \DateTime('Mar 3rd 2002'),
            new \DateTime('Apr 4th 2003'),
        ];
        $types = ['date' => 'date'];

        $s = $this->getSelector($types)
            ->whereIn('date', $dates);

        $this->assertSame($this->getYaml(__FUNCTION__), $s->getSQL());
        $this->assertSame($dates, $s->getParamsRaw());
        $this->assertSame($this->getPlatformYamlParams(__FUNCTION__), $s->getParams());
    }

    public function testPrepare()
    {
        $conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();
        $stmt = $this->getMock('Doctrine\DBAL\Driver\Statement');
        $conn->expects($this->once())
             ->method('prepare')
             ->with($this->getYaml(__FUNCTION__))
             ->will($this->returnValue($stmt));
        $s = $this->getSelectorWithMock($conn)
                  ->where('foo', '=', 'bar')
                  ->orWhere('id', '<', 400)
                  ->orderBy('foo');
        $this->assertSame($stmt, $s->prepare());
    }

    public function testExecute()
    {
        $conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();

        $stmt = $this->getMock('Doctrine\DBAL\Driver\Statement');
        $stmt->expects($this->once())
             ->method('execute')
             ->with(['bar', 400]);
        $result = [
            ['foo' => 'bar', 'id' => 30],
            ['foo' => 'bar', 'id' => 40],
        ];
        $stmt->expects($this->once())
             ->method('fetchAll')
             ->will($this->returnValue($result));

        $conn->expects($this->once())
             ->method('prepare')
             ->with($this->getYaml(__FUNCTION__))
             ->will($this->returnValue($stmt));

        $s = $this->getSelectorWithMock($conn)
                  ->where('foo', '=', 'bar')
                  ->orWhere('id', '<', 400)
                  ->orderBy('foo');
        $this->assertSame($result, $s->execute());
    }
}
