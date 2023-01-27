<?php

namespace App\Service;

use App\Entity\Relation;
use App\Repository\RelationRepository;

class RelationService
{
    public function __construct
    (
        private readonly RelationRepository $relationRepository,
        private readonly SerializerService $serializerService
    )
    {
    }

    public function create($relation): void
    {
        // check errors
        $this->serializerService->checkErrors($relation);
        // save
        $this->relationRepository->save($relation, true);
    }

    public function update(Relation $relation, $updatedRelation): void
    {
        // update
        $relation->setName($updatedRelation->getName());
        // check errors
        $this->serializerService->checkErrors($relation);
        // save
        $this->relationRepository->save($relation, true);
    }
}