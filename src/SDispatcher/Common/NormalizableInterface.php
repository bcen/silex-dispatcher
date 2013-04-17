<?php
namespace SDispatcher\Common;

interface NormalizableInterface
{
    /**
     * Returns normalized data.
     * @param mixed $option
     * @return mixed
     */
    public function normalize($option = null);
}
