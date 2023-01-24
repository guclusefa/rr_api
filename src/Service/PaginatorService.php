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
            'page' => (int) $page,
            'limit' => (int) $limit,
            'pages' => (int) ceil($paginator->count() / $limit),
            'total' => $paginator->count(),
            'start' => ($page - 1) * $limit + 1,
            'end' => $page * $limit,
        ];
    }
}