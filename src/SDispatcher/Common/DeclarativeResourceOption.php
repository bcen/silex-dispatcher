<?php
namespace SDispatcher\Common;

use ReflectionObject;

/**
 * Used to read resource option in a more declarative way.
 */
final class DeclarativeResourceOption extends AbstractResourceOption
{
    /**
     * Used to read private method/property.
     * @var \ReflectionObject
     */
    private $objectReflector;

    /**
     * The target object to read.
     * @var mixed
     */
    private $object;

    /**
     * The optional prefix for reading option.
     * @var string
     */
    private $prefix = '';

    /**
     * Used to cache the options.
     * @var array
     */
    private $cache = array();

    /**
     * @param $object
     * @param string $prefix
     */
    public function __construct($object, $prefix = '')
    {
        $this->object = $object;
        $this->objectReflector = new ReflectionObject($object);
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    protected function tryReadOption($name, &$out, $default = null)
    {
        $success = false;
        $name = $this->prefix . $name;
        $out = $default;
        if (isset($this->cache[$name])
            || array_key_exists($name, $this->cache)
        ) {
            $out = $this->cache[$name];
            $success = true;
        } elseif ($this->objectReflector->hasProperty($name)) {
            $out = $this->readFromProperty($name);
            $success = true;
            $this->cache[$name] = $out;
        } elseif ($this->objectReflector->hasMethod($name)) {
            $out = $this->readFromMethod($name);
            $success = true;
            $this->cache[$name] = $out;
        }
        return $success;
    }

    /**
     * {@inheritdoc}
     */
    protected function tryWriteOption($name, $value)
    {
        $name = $this->prefix . $name;
        $this->cache[$name] = $value;
    }

    private function readFromProperty($name)
    {
        $prop = $this->objectReflector->getProperty($name);
        $prop->setAccessible(true);
        return $prop->getValue($this->object);
    }

    private function readFromMethod($name)
    {
        $method = $this->objectReflector->getMethod($name);
        $method->setAccessible(true);
        return $method->invoke($this->object);
    }
}
