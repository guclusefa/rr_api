<?php

namespace App\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatorService
{
    public function paginate($qb, $page, $limit): Paginator
    {
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    public function getMetadata($paginator, $page, $limit): array
    {
        return [
            'total' => $paginator->count(),
            'page' => (int) $page,
            'pages' => (int) ceil($paginator->count() / $limit),
            'limit' => (int) $limit,
            'start' => min(($page - 1) * $limit + 1, $paginator->count()),
            'end' => min($page * $limit, $paginator->count()),
            'next' => $page * $limit < $paginator->count(),
            'previous' => $page > 1,
        ];
    }
}