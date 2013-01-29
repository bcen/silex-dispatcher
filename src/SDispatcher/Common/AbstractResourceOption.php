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
    public function getPaginatorClass()
    {
        $this->tryReadOption(
            'paginatorClass',
            $out,
            'SDispatcher\\Common\\InMemoryPaginator'
        );
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaginatorClass($paginatorClass)
    {
        if (!is_subclass_of($paginatorClass,
            'SDispatcher\\Common\\PaginatorInterface')
        ) {
            throw new \InvalidArgumentException(
                '$paginatorClass must implement ' .
                'SDispatcher\\Common\\PaginatorInterface'
            );
        }
        $this->tryWriteOption('paginatorClass', $paginatorClass);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceIdentifier()
    {
        $this->tryReadOption(
            'resourceIdentifier',
            $out,
            'id'
        );
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceIdentifier($id)
    {
        $this->tryWriteOption('resourceIdentifier', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatedDataContainerName()
    {
        $this->tryReadOption(
            'paginatedDataContainerName',
            $out,
            'objects'
        );
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaginatedDataContainerName($name)
    {
        $this->tryWriteOption('paginatedDataContainerName', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatedMetaContainerName()
    {
        $this->tryReadOption(
            'paginatedMetaContainerName',
            $out,
            'meta'
        );
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaginatedMetaContainerName($name)
    {
        $this->tryWriteOption('paginatedMetaContainerName', $name);
    }
}
