<?php
/**S
 * User: Administrator
 * Date: 2016-11-24
 * Time: 下午 2:02
 */

namespace Restore\test;

use Restore\Container;
use Restore\test\testCase\Atest;
use Restore\test\testCase\Btest;
use Restore\test\testCase\Ctest;

require_once "../vendor/autoload.php";

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClass()
    {
        $c = new Container();
        $this->assertEquals(new Atest(), $c->getClass(__NAMESPACE__."\\testCase\\Atest"));
    }

    public function testGetClass2()
    {
        $c = new Container();
        $this->assertEquals(new Ctest(new Btest(new Atest()), new Atest()), $c->getClass(__NAMESPACE__."\\testCase\\Ctest"));
    }

    public function testInstance()
    {
        $c = new Container();
        $this->assertEquals( $c->getClass(__NAMESPACE__."\\testCase\\Atest", [125]), $c->getClass(__NAMESPACE__."\\testCase\\Atest", [123]));
        $c->delete(__NAMESPACE__."\\testCase\\Atest");
        $this->assertNotEquals( $c->getClass(__NAMESPACE__."\\testCase\\Atest", [125], false), $c->getClass(__NAMESPACE__."\\testCase\\Atest", [123]));
    }

    public function testForbidden()
    {
        $c = new Container();
        $c->forbidden(__NAMESPACE__."\\testCase\\Ctest");
        $this->assertFalse($c->getClass(__NAMESPACE__."\\testCase\\Ctest"));
    }
    public function testFactory()
    {
        $c = new Container();
        $c->addFactory("a", new Atest());
        $this->assertEquals(new Atest(), $c->getClass("a"));
    }

    public function testDelete()
    {
        $c = new Container();
        $obj = $c->getClass(__NAMESPACE__."\\testCase\\Btest", [new Atest(123)]);
        $c->delete(__NAMESPACE__."\\testCase\\Btest");
        $this->assertNotEquals($obj, $c->getClass(__NAMESPACE__."\\testCase\\Btest", [new Atest(124)]));
    }
}
