<?php
/**
 * User: Administrator
 * Date: 2016-11-24
 * Time: 下午 6:49
 */

namespace Restore\test\testCase;


class Ctest
{
    public $b;
    public $a;
    public function __construct(Btest $b, Atest $a)
    {
        $this->b = $b;
        $this->c = $a;
    }
}