<?php

namespace App\Controller;

use App\Entity\State;
use App\Entity\User;
use App\Service\JWTService;
use App\Service\MailerService;
use App\Service\SearcherService;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly SearcherService $searcherService,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('', name: 'api_users', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $fieldsToSearchFrom = ['username', 'firstName', 'lastName'];
        $defaultFilters = ['isBanned' => false];
        $fieldsToFilterFrom = ['gender', 'state'];
        $fieldsToOrderFrom = ['id','username', 'createdAt'];
        // search by criterias
        $users = $this->searcherService->fullyFilteredData(
            $request->query->all(),
            $fieldsToSearchFrom,
            $defaultFilters,
            $fieldsToFilterFrom,
            $fieldsToOrderFrom,
            User::class
        );
        // serialize & return
        $users = $this->serializerService->serialize(User::GROUP_GET, $users);
        return new JsonResponse($users, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        // serialize & return
        $user = $this->serializerService->serialize(User::GROUP_ITEM, $user);
        return new JsonResponse($this->serializerService->getSerializedData($user), Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_users_update', methods: ['PUT'])]
    public function update(User $user, Request $request): JsonResponse
    {
        // deserialize & validate
        $updatedUser = $this->serializerService->deserialize(User::GROUP_UPDATE, $request, User::class);
        // update
        $user->setUsername($updatedUser->getUsername());
        $user->setFirstName($updatedUser->getFirstName());
        $user->setLastName($updatedUser->getLastName());
        $user->setGender($updatedUser->getGender());
        $user->setBirthDate($updatedUser->getBirthDate());
        $user->setBio($updatedUser->getBio());
        // TODO : update avatar
        $base64Photo = $updatedUser->getPhoto();
        if ($base64Photo) {
            // convert base64 to image
            $image = file_get_contents($base64Photo);
            $imageName = $user->getId() . '.png';
            $imagePath = "uploads/users/images/" . $imageName;
            file_put_contents($imagePath, $image);
            $user->setPhoto($imageName);
        }
        $state = $this->entityManager->getRepository(State::class)->findBy(['id' => $updatedUser->getState()->getId()]);
        if ($state) {
            $user->setState($updatedUser->getState());
        } else {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Département invalide');
        }
        // check
        $this->serializerService->validate($user);
        // persist & flush
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // photo is absolute path
        $user->setPhoto($request->getSchemeAndHttpHost() . '/uploads/users/images/' . $user->getPhoto());
        // serialize & return
        $user = $this->serializerService->serialize(User::GROUP_ITEM, $user);
        return new JsonResponse($user, Response::HTTP_OK, [], true);
    }
}