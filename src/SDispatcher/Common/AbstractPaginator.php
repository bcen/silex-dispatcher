<?php
namespace SDispatcher\Common;

use SDispatcher\Common\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPaginator implements PaginatorInterface
{
    /**
     * Hook point for validating the queryset.
     * @param mixed $queryset
     * @return mixed
     */
    abstract protected function validateQueryset($queryset);

    /**
     * Slices the queryset according to $offset, and $limit.
     * @param mixed $queryset
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    abstract protected function slice($queryset, $offset, $limit);

    /**
     * Counts the total results from queryset.
     * @param mixed $queryset
     * @return int
     */
    abstract protected function countTotal($queryset);

    /**
     * Creates a next page link if availiable.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $offset
     * @param int $limit
     * @return string mixed
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
     * Creates a previous page link if avialiable.
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $offset
     * @param int $limit
     * @param int $total
     * @return \Symfony\Component\HttpFoundation\Request mixed
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
        $this->validateQueryset($queryset);

        $offset = (int)$request->query->get(
            'offset',
            $request->headers->get('X-Pagination-Offset', $defaultOffset)
        );
        $limit = (int)$request->query->get(
            'limit',
            $request->headers->get('X-Pagination-Limit', $defaultLimit)
        );

        $objects = $this->slice($queryset, $offset, $limit);
        $total = $this->countTotal($queryset);
        $prevLink = $this->createPrevLink($request, $offset, $limit);
        $nextLink = $this->createNextLink($request, $offset, $limit, $total);

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
