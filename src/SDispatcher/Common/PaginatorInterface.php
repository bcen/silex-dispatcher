<?php
namespace SDispatcher\Common;

/**
 * Used to paginate bundle data in {@link \SDispatcher\DispatchableResource}.
 */
interface PaginatorInterface
{
    /**
     * Returns the total count of the data.
     * @return int
     */
    public function getCount();

    /**
     * Returns the current paginated data.
     * @return mixed
     */
    public function getPage();

    /**
     * Returns the queryset for querying data.
     * @return mixed
     */
    public function getQueryset();

    /**
     * Sets the queryset.
     * @param mixed $queryset
     * @return \SDispatcher\Common\PaginatorInterface
     */
    public function setQueryset($queryset);

    /**
     * Returns the current page offset.
     * @return int mixed
     */
    public function getOffset();

    /**
     * Sets the offset.
     * @param int $offset
     * @return \SDispatcher\Common\PaginatorInterface
     */
    public function setOffset($offset);

    /**
     * Returns the current page limit.
     * @return int
     */
    public function getLimit();

    /**
     * Sets the page limit.
     * @param int $limit
     * @return \SDispatcher\Common\PaginatorInterface
     */
    public function setLimit($limit);
}
