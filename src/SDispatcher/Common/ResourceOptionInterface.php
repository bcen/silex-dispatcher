<?php
namespace SDispatcher\Common;

use SDispatcher\Common\PaginatorInterface;

interface ResourceOptionInterface
{
    /**
     * Sets the target to inspect.
     * @param mixed $classOrObj
     * @param string $method
     * @return void
     */
    public function setTarget($classOrObj, $method = null);

    /**
     * Returns the default supported mime format.
     * @return string
     */
    public function getDefaultFormat();

    /**
     * Returns the supported formats.
     * @return array
     */
    public function getSupportedFormats();

    /**
     * Sets the supported mime formats.
     * @param array $formats
     * @return \SDispatcher\Common\ResourceOptionInterface
     */
    public function setSupportedFormats(array $formats);

    /**
     * Returns the the page limit.
     * @return int
     */
    public function getPageLimit();

    /**
     * Sets the page limit.
     * @param $limit
     * @return \SDispatcher\Common\ResourceOptionInterface
     */
    public function setPageLimit($limit);

    /**
     * Returns the supported request method.
     * @return array
     */
    public function getAllowedMethods();

    /**
     * Sets the supported request method.
     * @param array $methods
     * @return \SDispatcher\Common\ResourceOptionInterface
     */
    public function setAllowedMethods(array $methods);

    /**
     * Returns the paginator instance.
     * @deprecated
     * @return \SDispatcher\Common\PaginatorInterface
     */
    public function getPaginator();

    /**
     * Sets the paginator instance.
     * @deprecated
     * @param PaginatorInterface $paginator
     * @return \SDispatcher\Common\ResourceOptionInterface
     */
    public function setPaginator(PaginatorInterface $paginator);

    /**
     * Returns the paginator class tring
     * @return string
     */
    public function getPaginatorClass();

    /**
     * Sets the paginator class string
     * @param string $paginatorClass
     * @return \SDispatcher\Common\ResourceOptionInterface
     */
    public function setPaginatorClass($paginatorClass);

    /**
     * Returns the identifier name for dehydration.
     * @return string
     */
    public function getResourceIdentifier();

    /**
     * Sets the identifier name for dehydration.
     * @param string $id
     * @return \SDispatcher\Common\ResourceOptionInterface
     */
    public function setResourceIdentifier($id);

    /**
     * Returns the paginated data container name.
     * @return string
     */
    public function getPaginatedDataContainerName();

    /**
     * Sets the paginated data container name.
     * @param string $name
     * @return \SDispatcher\Common\ResourceOptionInterface
     */
    public function setPaginatedDataContainerName($name);

    /**
     * Returns the paginated meta data container name.
     * @return string
     */
    public function getPaginatedMetaContainerName();

    /**
     * Sets the paginated meta data container name.
     * @param string $name
     * @return \SDispatcher\Common\ResourceOptionInterface
     */
    public function setPaginatedMetaContainerName($name);
}
