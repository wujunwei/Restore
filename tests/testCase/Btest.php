<?php
/**
 * User: Administrator
 * Date: 2016-11-24
 * Time: 下午 6:49
 */

namespace Restore\test\testCase;


class Btest
{
    public $a;
    public function __construct(Atest $a)
    {
        $this->a = $a;
    }
}