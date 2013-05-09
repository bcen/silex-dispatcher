<?php
namespace SDispatcher\Common;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

/**
 * A wrapper adatper around Doctrine ORM Paginator.
 */
class DoctrineOrmPaginatorAdapter extends AbstractPaginator
{
    /**
     * {@inheritdoc}
     */
    public function supports($queryset)
    {
        return ($queryset instanceof Paginator);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateQueryset($queryset)
    {
        if (!$queryset instanceof Paginator) {
            throw new \InvalidArgumentException(
                '$queryset must be instance of Paginator');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function slice($queryset, $offset, $limit, Request $request)
    {
        /* @var \Doctrine\ORM\Tools\Pagination\Paginator $queryset */
        $queryset->getQuery()->setFirstResult($offset)->setMaxResults($limit);
        $data = array();
        foreach ($queryset as $obj) {
            if ($obj instanceof NormalizableInterface) {
                $data[] = $obj->normalize($request->query->all());
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function countTotal($queryset)
    {
        /* @var \Doctrine\ORM\Tools\Pagination\Paginator $queryset */
        $queryset->getQuery()->setFirstResult(null)->setMaxResults(null);
        return $queryset->count();
    }
}
