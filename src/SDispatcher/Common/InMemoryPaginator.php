<?php
namespace SDispatcher\Common;

use Symfony\Component\HttpFoundation\Request;

/**
 * Paginates data in memory.
 * <i>Note: queryset will be the actual data in memory.</i>
 */
class InMemoryPaginator implements PaginatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function paginate(Request $request,
                             $queryset,
                             $defaultOffset = 0,
                             $defaultLimit = 20,
                             $metaContainerName = 'meta',
                             $objectContainerName = 'objects')
    {
        if (!is_array($queryset)) {
            throw new \LogicException('$queryset must be an array');
        }

        $offset = (int)$request->query->get(
            'offset',
            $request->headers->get('X-Pagination-Offset', $defaultOffset)
        );
        $limit = (int)$request->query->get(
            'limit',
            $request->headers->get('X-Pagination-Offset', $defaultLimit)
        );

        $objects = array_slice(
            $queryset,
            $offset,
            $limit
        );

        $total = count($queryset);

        $prevLink = null;
        $nextLink = null;
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

        if ($offset + $limit < $total) {
            parse_str($request->getQueryString(), $qsArray);
            $qsArray['limit'] = $limit;
            $qsArray['offset'] = $limit + $offset;
            $qs = Request::normalizeQueryString(http_build_query($qsArray));
            $nextLink = $baseUri . '?' . $qs;
        }

        // top level container
        $data = array();

        // meta data container
        $metaContainer = array();
        $metaContainer['offset'] = $offset;
        $metaContainer['limit'] = $limit;
        $metaContainer['total'] = $total;
        $metaContainer['prevLink'] = $prevLink;
        $metaContainer['nextLink'] = $nextLink;

        if ($metaContainerName !== '' && is_string($metaContainerName)) {
            $data[$metaContainerName] = $metaContainer;
        } else {
            $data = $metaContainer;
        }

        // result objects container
        $data[$objectContainerName] = $objects;

        $headers = array(
            'X-Pagination-Offset' => $offset,
            'X-Pagination-Limit' => $limit,
            'X-Pagination-Total' => $total,
            'X-Pagination-PrevLink' => $prevLink,
            'X-Pagination-NextLink' => $nextLink
        );

        return array($headers, $data);
    }
}
