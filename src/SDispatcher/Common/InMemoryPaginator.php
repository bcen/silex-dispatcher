<?php
namespace SDispatcher\Common;

use Symfony\Component\HttpFoundation\Request;

/**
 * Paginates data in memory.
 * <i>Note: queryset will be the actual data in memory.</i>
 */
class InMemoryPaginator extends AbstractPaginator
{
    /**
     * {@inheritdoc}
     */
    protected function validateQueryset($queryset)
    {
        if (!is_array($queryset)) {
            throw new \LogicException('$queryset must be an array.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function slice($queryset, $offset, $limit)
    {
        return array_slice($queryset, $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    protected function countTotal($queryset)
    {
        return count($queryset);
    }
}
