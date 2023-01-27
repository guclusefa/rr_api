<?php

namespace App\Service;

use App\Entity\State;
use App\Repository\StateRepository;

class StateService
{
    public function __construct
    (
        private readonly StateRepository $stateRepository,
        private readonly SerializerService $serializerService
    )
    {
    }

    public function create($state): void
    {
        // check errors
        $this->serializerService->checkErrors($state);
        // save
        $this->stateRepository->save($state, true);
    }

    public function update(State $state, $updatedState): void
    {
        // update
        $state->setName($updatedState->getName());
        $state->setCode($updatedState->getCode());
        // check errors
        $this->serializerService->checkErrors($state);
        // save
        $this->stateRepository->save($state, true);
    }
}