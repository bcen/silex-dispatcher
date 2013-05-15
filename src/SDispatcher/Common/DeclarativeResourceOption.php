<?php
namespace SDispatcher\Common;

/**
 * Used to read resource option in a more declarative way.
 */
final class DeclarativeResourceOption extends AbstractResourceOption
{
    /**
     * Used to read private method/property.
     * @var \ReflectionObject
     */
    private $reflector;

    /**
     * The target object to read.
     * @var mixed
     */
    private $classOrObj;

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
     * {@inheritdoc}
     */
    public function setTarget($classOrObj, $method = null)
    {
        $this->classOrObj = $classOrObj;
        if (is_string($classOrObj) && class_exists($classOrObj)) {
            $this->reflector = new \ReflectionClass($classOrObj);
        } elseif (is_object($classOrObj)) {
            $this->reflector = new \ReflectionObject($classOrObj);
        }
    }

    /**
     * Sets the prefix of all option attribute.
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
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

        if (!$this->classOrObj) {
            return false;
        }

        if (isset($this->cache[$name])
            || array_key_exists($name, $this->cache)
        ) {
            $out = $this->cache[$name];
            $success = true;
        } elseif ($this->reflector->hasProperty($name)) {
            $out = $this->readFromProperty($name);
            $success = true;
            $this->cache[$name] = $out;
        } elseif ($this->reflector->hasMethod($name)) {
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
        $prop = $this->reflector->getProperty($name);
        $prop->setAccessible(true);
        return $prop->getValue($this->classOrObj);
    }

    private function readFromMethod($name)
    {
        $method = $this->reflector->getMethod($name);
        $method->setAccessible(true);
        return $method->invoke($this->classOrObj);
    }
}
