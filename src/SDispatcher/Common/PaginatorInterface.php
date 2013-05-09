<?php
namespace SDispatcher\Common;

use Symfony\Component\HttpFoundation\Request;

/**
 * Used to paginate bundle data in {@link \SDispatcher\DispatchableResource}.
 */
interface PaginatorInterface
{
    /**
     * Whether the `$queryset` is supported or not.
     * @param mixed $queryset
     * @return bool
     */
    public function supports($queryset);

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $queryset
     * @param int $defaultOffset
     * @param int $defaultLimit
     * @param string $metaContainerName
     * @param string $objectContainerName
     * @return array array($headers, $data)
     */
    public function paginate(Request $request,
                             $queryset,
                             $defaultOffset = 0,
                             $defaultLimit = 20,
                             $metaContainerName = 'meta',
                             $objectContainerName = 'objects');
}
