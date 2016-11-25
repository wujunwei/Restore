<?php
/**
 * User: wjw33
 * Date: 2016-11-20
 * Time: 9:56
 */

namespace Restore;


class Container
{

    protected $factory;
    protected $instances;
    protected $keys;
    protected $forbidden;

    /**
     * Container constructor.
     * @param array $forbidden
     */
    public  function __construct(array $forbidden = [])
    {
        $this->init();
        foreach($forbidden as $key => $value) {
            $this->forbidden[$value] = true;
        }
    }

    protected function set($key, $value)
    {
        $this->keys[$key] = true;
        $this->instances[$key] = $value;
    }

    protected function keyExist($key)
    {
        return isset($this->keys[$key]);
    }

    protected function reflectClass($className)
    {
        try {
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new \ReflectionException(sprintf('Class "%s" is invalid', $className));
        }
        return $reflection;
    }

    protected function create($class, array $params = [])
    {
        $reflect = $this->reflectClass($class);
        $constructor = $reflect->getConstructor();
        $params = $this->dealWithParams($constructor, $params);
        if (!is_null($constructor)) {
            $instance = $reflect->newInstanceArgs($params);
        } else {
            $instance = $reflect->newInstanceWithoutConstructor();
        }
        return $instance;
    }

    protected function factory($key, $params)
    {
        $callback = $this->factory[$key];
        $reflectFunc = new \ReflectionFunction($callback);
        if ($reflectFunc->getNumberOfRequiredParameters() > count($params)) {
            throw new \InvalidArgumentException('The parameters provide %d but  require %d.', count($params),$reflectFunc->getNumberOfRequiredParameters() );
        }
        $instance = call_user_func_array($callback, $params);
        if (!is_null($instance)) {
            $this->set($key, $instance);
            return $instance;
        } else {
            throw new \InvalidArgumentException('Extension service definition is null.');
        }
    }

    /**
     * add create function
     * @param $key
     * @param $callable
     */
    public function addFactory($key, $callable)
    {
        if (!$this->keyExist($key)) {
            if(!$callable instanceof \Closure) {
                $callable = function () use($callable) {
                    return $callable;
                };
            }
            $this->factory[$key] = $callable;
            $this->keys[$key] = true;
        }
    }

    /**
     * 返回对应的类实例
     * @param $class
     * @param array $params
     * @param bool $isInstance
     * @return false|object
     */
    public function getClass($class, array $params = [], $isInstance = true)
    {
        if (isset($this->forbidden[$class])){
            return false;
        }
        if ($this->keyExist($class)) {
            if (isset($this->instances[$class])) {
                return $this->instances[$class];
            } else if ($this->factory[$class] instanceof \Closure) {
                return $this->factory($class, $params);
            }
        }
        $instance = $this->create($class, $params);
        if ($isInstance) {
            $this->set($class, $instance);
        }
        return $instance;
    }

    public function init()
    {
        $this->instances = [];
        $this->keys = [];
        $this->factory = [];
        $this->forbidden = [];
    }

    /**
     * 禁用某些类
     * @param $key
     */
    public function forbidden($key)
    {
        $this->forbidden[$key] = true;
        $this->delete($key);
    }

    public function delete($key)
    {
        unset($this->instances[$key]);
        unset($this->keys[$key]);
        unset($this->factory[$key]);
    }

    /**
     * 处理参数自动处理依赖
     * @param \ReflectionMethod $constructor
     * @param array $params
     * @return array
     */
    private function dealWithParams(\ReflectionMethod $constructor, $params)
    {
        $parameters = $constructor->getParameters();
        $count = count($params);
        $result = [];
        foreach ($parameters as $key => $p) {
            $paramsName = $p->getName();
            if($count > $key) {
                if (($p->isArray() && !is_array($params[$key]))  ||
                    (!is_object($params[$key]) && $p->getClass() != null)||
                    (is_object($params[$key]) && $p->getClass()->getName() != get_class($params[$key]))) {
                    throw new \InvalidArgumentException(sprintf('The type of parameter %s are not the same as required.', $paramsName));
                } else {
                    $result[] = $params[$key];
                }
            } else {
                if (!$p->isDefaultValueAvailable()) {
                    $className = $p->getClass();
                    if ($className !== null){
                        $result[] = $this->getClass($className->getName());
                    }else{
                        throw new \InvalidArgumentException(sprintf('The type of %d parameter %s are not the same as required.', $key, $paramsName));
                    }
                } else {
                    break;
                }
            }
        }
        return $result;
    }

}
