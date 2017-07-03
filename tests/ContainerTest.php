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
        $this->assertEquals(new Atest(), $c->get(__NAMESPACE__."\\testCase\\Atest"));
    }

    public function testGetClass2()
    {
        $c = new Container();
        $this->assertEquals(new Ctest(new Btest(new Atest()), new Atest()), $c->get(__NAMESPACE__."\\testCase\\Ctest"));
    }

    public function testInstance()
    {
        $c = new Container();
        $this->assertEquals( $c->get(__NAMESPACE__."\\testCase\\Atest", [125]), $c->get(__NAMESPACE__."\\testCase\\Atest", [123]));
        $c->delete(__NAMESPACE__."\\testCase\\Atest");
        $this->assertNotEquals( $c->get(__NAMESPACE__."\\testCase\\Atest", [125], false), $c->get(__NAMESPACE__."\\testCase\\Atest", [123]));
    }

    public function testForbidden()
    {
        $c = new Container();
        $c->forbidden(__NAMESPACE__."\\testCase\\Ctest");
        $this->assertFalse($c->get(__NAMESPACE__."\\testCase\\Ctest"));
    }
    public function testFactory()
    {
        $c = new Container();
        $c->addFactory("a", new Atest());
        $this->assertEquals(new Atest(), $c->get("a"));
    }

    public function testDelete()
    {
        $c = new Container();
        $obj = $c->get(__NAMESPACE__."\\testCase\\Btest", [new Atest(123)]);
        $c->delete(__NAMESPACE__."\\testCase\\Btest");
        $this->assertNotEquals($obj, $c->get(__NAMESPACE__."\\testCase\\Btest", [new Atest(124)]));
    }
}
