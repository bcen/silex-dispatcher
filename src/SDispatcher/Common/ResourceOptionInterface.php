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

    public function getPaginatorClass();
    public function setPaginatorClass($class);
}
