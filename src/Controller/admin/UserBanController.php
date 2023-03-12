<?php

namespace App\Controller\admin;

use App\Entity\UserBan;
use App\Repository\UserBanRepository;
use App\Service\SerializerService;
use App\Service\UserBanService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/admin/bans')]
class UserBanController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly UserBanRepository $userBanRepository,
        private readonly TranslatorInterface $translator,
        private readonly UserBanService $userBanService
    )
    {
    }

    #[Route('', name: 'api_admin_users_bans', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $search = $request->query->get('search');
        // pagination
        $order = $request->query->get('order', 'id');
        $direction = $request->query->get('direction', 'ASC');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        // get, serialize & return
        $categories = $this->userBanRepository->advanceSearch($search, $order, $direction, $page, $limit);
        $categories = $this->serializerService->serialize(UserBan::GROUP_GET, $categories);
        return new JsonResponse(
            $categories,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_admin_users_bans_show', methods: ['GET'])]
    public function show(UserBan $userBan): JsonResponse
    {
        // get, serialize & return
        $userBan = $this->serializerService->serialize(UserBan::GROUP_ITEM, $userBan);
        return new JsonResponse(
            $userBan,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_admin_users_bans_update', methods: ['PUT'])]
    public function update(Request $request, UserBan $userBan): JsonResponse
    {
        // deserialize & update
        $updatedUserBan = $this->serializerService->deserialize(UserBan::GROUP_WRITE, $request, UserBan::class);
        $this->userBanService->update($userBan, $updatedUserBan);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.userban.updated_success')],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'api_admin_users_bans_delete', methods: ['DELETE'])]
    public function delete(UserBan $userBan): JsonResponse
    {
        // delete & return
        $this->userBanRepository->remove($userBan, true);
        return new JsonResponse(
            ['message' => $this->translator->trans('message.userban.deleted_success')],
            Response::HTTP_OK
        );
    }
}