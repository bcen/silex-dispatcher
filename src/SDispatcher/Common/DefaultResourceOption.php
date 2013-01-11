<?php
namespace SDispatcher\Common;

class DefaultResourceOption extends AbstractResourceOption
{
    private $options = array();

    /**
     * {@inheritdoc}
     */
    protected function tryReadOption($name, &$out, $default = null)
    {
        $out = $default;
        $success = false;
        if (isset($this->options[$name])
            || array_key_exists($name, $this->options)
        ) {
            $out = $this->options[$name];
            $success = true;
        }
        return $success;
    }

    /**
     * {@inheritdoc}
     */
    protected function tryWriteOption($name, $value)
    {
        $this->options[$name] = $value;
    }
}
