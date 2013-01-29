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

    /**
     * {@inheritdoc}
     */
    protected function createPrevLink(Request $request, $offset, $limit)
    {
        $prevLink = null;
        $baseUri = $request->getSchemeAndHttpHost() .
                   $request->getBaseUrl() .
                   $request->getPathInfo();
        if ($offset - $limit >= 0) {
            parse_str($request->getQueryString(), $qsArray);
            $qsArray['limit'] = $limit;
            $qsArray['offset'] = $offset - $limit;
            $qs = Request::normalizeQueryString(http_build_query($qsArray));
            $prevLink = $baseUri . '?' . $qs;
        }
        return $prevLink;
    }

    /**
     * {@inheritdoc}
     */
    protected function createNextLink(Request $request, $offset, $limit, $total)
    {
        $nextLink = null;
        $baseUri = $request->getSchemeAndHttpHost() .
                   $request->getBaseUrl() .
                   $request->getPathInfo();
        if ($offset + $limit < $total) {
            parse_str($request->getQueryString(), $qsArray);
            $qsArray['limit'] = $limit;
            $qsArray['offset'] = $limit + $offset;
            $qs = Request::normalizeQueryString(http_build_query($qsArray));
            $nextLink = $baseUri . '?' . $qs;
        }
        return $nextLink;
    }
}
