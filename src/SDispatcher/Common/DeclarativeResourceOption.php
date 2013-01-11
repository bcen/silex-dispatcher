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
        if ($this->objectReflector->hasProperty($name)) {
            $out = $this->readFromProperty($name);
            $success = true;
        } elseif ($this->objectReflector->hasMethod($name)) {
            $out = $this->readFromMethod($name);
            $success = true;
        }
        return $success;
    }

    /**
     * {@inheritdoc}
     */
    protected function tryWriteOption($name, $value)
    {
        $name = $this->prefix . $name;
        if ($this->objectReflector->hasProperty($name)) {
            $prop = $this->objectReflector->getProperty($name);
            $prop->setAccessible(true);
            $prop->setValue($this->object, $value);
        }
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
