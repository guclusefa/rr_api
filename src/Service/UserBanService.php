<?php

namespace App\Service;

use App\Entity\UserBan;
use App\Repository\UserBanRepository;

class UserBanService
{
    public function __construct
    (
        private readonly UserBanRepository $userBanRepository,
        private readonly SerializerService $serializerService
    )
    {
    }

    public function update(UserBan $userBan, $updatedUserBan): void
    {
        // update
        $userBan->setReason($updatedUserBan->getReason());
        $userBan->setEndDate($updatedUserBan->getEndDate());
        // check errors
        $this->serializerService->checkErrors($userBan);
        // save
        $this->userBanRepository->save($userBan, true);
    }
}