<?php
namespace SDispatcher\Common;

abstract class AbstractResourceOption implements ResourceOptionInterface
{
    /**
     * Tries to read the option from source with the given $name, and
     * stories it in $out.
     * @param string $name The name of the option
     * @param mixed $out The output
     * @param null|mixed $default The default value for the given option
     * @return bool true, if success; otherwise, false
     */
    abstract protected function tryReadOption($name, &$out, $default = null);

    /**
     * Tries to write the option to source.
     * @param string $name The name of the option
     * @param mixed $value The value to write
     * @return bool true, if success, otherwise, false
     */
    abstract protected function tryWriteOption($name, $value);

    /**
     * {@inheritdoc}
     */
    public function getDefaultFormat()
    {
        $formats = $this->getSupportedFormats();
        if (is_array($formats) && !empty($formats)) {
            return array_shift($formats);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedFormats()
    {
        $this->tryReadOption(
            'supportedFormats',
            $out,
            array('application/json')
        );
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function setSupportedFormats(array $formats)
    {
        if (!empty($formats)) {
            $this->tryWriteOption('supportedFormats', $formats);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageLimit()
    {
        $this->tryReadOption(
            'pageLimit',
            $out,
            20
        );
        return (int)$out;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageLimit($limit)
    {
        $this->tryWriteOption('pageLimit', $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        $this->tryReadOption(
            'allowedMethods',
            $out,
            array('GET')
        );
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowedMethods(array $methods)
    {
        $this->tryWriteOption('allowedMethods', $methods);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginator()
    {
        $this->tryReadOption(
            'paginator',
            $out,
            new InMemoryPaginator()
        );
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->tryWriteOption('paginator', $paginator);
    }
}
