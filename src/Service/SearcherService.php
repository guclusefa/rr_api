<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

// DEPRECATED
class SearcherService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function getSearchFromQuery($query): string
    {
        return $query['search'] ?? '';
    }

    public function getFiltersFromQuery($query, $filters, $fieldsToFilterFrom): array
    {
        foreach ($fieldsToFilterFrom as $field){
            if (isset($query[$field])){
                $filters[$field] = $query[$field];
            }
        }
        return $filters;
    }

    public function getOrderFromQuery($query, $fieldsToOrderFrom): string
    {
        if (isset($query['order'])) {
            $order = $query['order'];
            if (in_array($order, $fieldsToOrderFrom)) {
                return $order;
            }
        }
        return $fieldsToOrderFrom[0];
    }

    public function getDirectionFromQuery($query): string
    {
        $directions = ['DESC', 'ASC'];
        if (isset($query['direction'])) {
            $direction = $query['direction'];
            if (in_array($direction, $directions)) {
                return $direction;
            }
        }
        return $directions[0];
    }

    public function getPageFromQuery($query): int
    {
        if (isset($query['page'])) {
            $page = $query['page'];
            if (is_numeric($page)) {
                return (int) $page;
            }
        }
        return 1;
    }

    public function getLimitFromQuery($query): int
    {
        if (isset($query['limit'])) {
            $limit = $query['limit'];
            if (is_numeric($limit)) {
                return (int) $limit;
            }
        }
        return 10;
    }

    public function searchFromFields($qb, $fieldsToSearchFrom, $search): void
    {
        if ($search) {
            for($i = 0; $i < count($fieldsToSearchFrom); $i++){
                if($i === 0){
                    $qb->andWhere('o.'.$fieldsToSearchFrom[$i].' LIKE :search');
                } else {
                    $qb->orWhere('o.'.$fieldsToSearchFrom[$i].' LIKE :search');
                }
            }
            $qb->setParameter('search', '%'.$search.'%');
        }
    }

    public function filterFromFilters($qb, $filters): void
    {
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $qb->andWhere('o.'.$key.' IN (:'.$key.')');
            } else {
                $qb->andWhere('o.'.$key.' = :'.$key);
            }
            $qb->setParameter($key, $value);
        }
    }

    public function createQueryBuilder($object): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()->select('o')->from($object, 'o')->where('true = true');
    }

    public function searchAndFilter($qb, $fieldsToSearchFrom, $search, $filters): QueryBuilder
    {
        // search & filter
        $this->searchFromFields($qb, $fieldsToSearchFrom, $search);
        $this->filterFromFilters($qb, $filters);
        // result
        return $qb;
    }

    public function orderAndPaginate($qb, $order, $direction, $page, $limit): QueryBuilder
    {
        // order & paginate
        $qb->orderBy('o.'.$order, $direction);
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);
        // result
        return $qb;
    }

    public function getMetaData($qb, $page, $limit): array
    {
        $total = count($qb->getQuery()->getResult());
        // get the index of first item and last item of the page
        $start = ($page - 1) * $limit + 1;
        $end = $start + $limit - 1;
        // if the last item is greater than the total, set the last item to the total
        if ($end > $total) $end = $total;
        $pages = (int) ceil($total / $limit);
        $next = $page < $pages;
        $previous = $page > 1;
        return [
            'total' => $total,
            'start' => $start,
            'end' => $end,
            'page' => $page,
            'pages' => $pages,
            'next' => $next,
            'previous' => $previous,
            'limit' => $limit,
        ];
    }

    public function fullyFilteredData($query, $fieldsToSearchFrom, $defaultFilters, $fieldsToFilterFrom, $fieldsToOrderFrom, $object): array
    {
        // criterias
        $search = $this->getSearchFromQuery($query);
        $filters = $this->getFiltersFromQuery($query, $defaultFilters, $fieldsToFilterFrom);
        $order = $this->getOrderFromQuery($query, $fieldsToOrderFrom);
        $direction = $this->getDirectionFromQuery($query);
        $page = $this->getPageFromQuery($query);
        $limit = $this->getLimitFromQuery($query);
        // query
        $qb = $this->createQueryBuilder($object);
        $qb = $this->searchAndFilter($qb, $fieldsToSearchFrom, $search, $filters);
        $qb = $this->orderAndPaginate($qb, $order, $direction, $page, $limit);
        // result
        return [
            'data' => $qb->getQuery()->getResult(),
            'meta' => $this->getMetaData($qb, $page, $limit),
        ];
    }
}