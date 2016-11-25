<?php
/**
 * User: wjw33
 * Date: 2016-11-20
 * Time: 13:38
 */

namespace Restore;

class HashNode
{
    public $key;
    public $obj;
    public $nextNode;
    public function __construct($key, $obj, HashNode $next = null)
    {
        $this->key = $key;
        $this->obj = $obj;
        $this->nextNode = $next;
    }
}


class Bucket implements \Countable
{
    const BUCKET_SIZE = 256;
    private $list;
    private $num;
    public function __construct()
    {
        $this->list = new \SplFixedArray(self::BUCKET_SIZE);
        $this->num = 0;
}

    /**
     * @param string $string
     * times 33
     * @return int
     */
    private function getIndex($string)
    {
        $string = substr(md5($string), 0, 8);
        $hash = 0;
        for ($i = 0;$i < 8; $i ++) {
            $hash += 33 * $hash + ord($string{$i});
        }
        return $hash & 0xFF;
    }

    /**
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function insert($key, $value)
    {
        $index = $this->getIndex($key);
        if(empty($this->list[$index])) {
            $this->list[$index] = new HashNode($key, $value);
        } else {
            $node = $this->list[$index];
            $this->list[$index] = new HashNode($key, $value, $node);
        }
        $this->num ++;
        return $value;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function find($key)
    {
        $index = $this->getIndex($key);
        $node = $this->list[$index];
        while ($node !== null && $node->key !== $key) {
            $node = $node->nextNode;
        }
        return empty($node)? null: $node->obj;
    }


    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->num;
    }
}
