<?php
namespace SDispatcher\Common;

/**
 * Paginates data in memory.
 * <i>Note: queryset will be the actual data in memory.</i>
 */
class InMemoryPaginator implements PaginatorInterface
{
    private $queryset;
    private $offset;
    private $limit;

    /**
     * Constructs with paginator instance with $offset and $limit.
     * @param int $offset
     * @param int $limit
     */
    public function __construct($offset = 0, $limit = 20)
    {
        $this->setOffset($offset)
             ->setLimit($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryset()
    {
        return $this->queryset;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryset($queryset)
    {
        $this->queryset = $queryset;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return count($this->queryset);
    }

    /**
     * {@inheritdoc}
     */
    public function getPage()
    {
        $objects = array_slice(
            $this->getQueryset(),
            $this->getOffset(),
            $this->getLimit()
        );

        $data['meta']['offset'] = (int)$this->getOffset();
        $data['meta']['limit'] = (int)$this->getLimit();
        $data['meta']['total'] = (int)$this->getCount();
        $data['objects'] = $objects;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }
}
