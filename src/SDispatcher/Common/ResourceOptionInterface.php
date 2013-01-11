<?php
namespace SDispatcher\Common;

interface ResourceOptionInterface
{
    public function getDefaultFormat();
    public function getSupportedFormats();
    public function setSupportedFormats(array $formats);

    public function getPageLimit();
    public function setPageLimit($limit);

    public function getAllowedMethods();
    public function setAllowedMethods(array $methods);

    /**
     * @return \SDispatcher\Common\PaginatorInterface
     */
    public function getPaginator();
    public function setPaginator(PaginatorInterface $paginator);
}
