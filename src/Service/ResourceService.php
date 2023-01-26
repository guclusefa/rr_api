<?php

namespace App\Service;

use App\Entity\Resource;
use App\Entity\ResourceConsult;
use App\Entity\ResourceExploit;
use App\Entity\ResourceLike;
use App\Entity\ResourceSave;
use App\Entity\ResourceShare;
use App\Entity\ResourceSharedTo;
use App\Entity\ResourceStats;
use App\Entity\User;
use App\Repository\ResourceRepository;
use App\Repository\ResourceStatsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResourceService
{
    public function __construct
    (
        private readonly ResourceRepository $resourceRepository,
        private readonly ResourceStatsRepository $resourceStatsRepository,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $params,
        private readonly FileUploaderService $fileUploaderService,
        private readonly SerializerService $serializerService,
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function checkAccess($resource, $me): void
    {
        if (!$this->resourceRepository->isAccesibleToMe($resource, $me)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $this->translator->trans('message.resource.access_denied'));
        }
    }

    public function checkCreateAccess($me): void
    {
        if (!$me->isIsVerified()) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $this->translator->trans('message.resource.access_create_denied'));
        }
    }

    public function checkUpdateAccess($resource, $me): void
    {
        if ($resource->getAuthor() !== $me || !$me->isIsVerified()) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $this->translator->trans('message.resource.access_update_denied'));
        }
    }

    public function formatResource($resource, $baseUrl): Resource
    {
        if ($resource->getMedia() != null) {
            $resource->setMedia($baseUrl . "/" . $this->params->get("app.resource.media.path") . $resource->getMedia());
        }
        return $resource;
    }

    public function formatResources($resources, $baseUrl): array
    {
        foreach ($resources["data"] as $resource) {
            $this->formatResource($resource, $baseUrl);
        }
        return $resources;
    }

    public function create($resource, $user): void
    {
        // create
        $resource->setAuthor($user);
        // check for errors
        $this->serializerService->checkErrors($resource);
        // check for errors
        $this->serializerService->checkErrors($resource);
        // save
        $this->resourceRepository->save($resource, true);
    }

    public function update(Resource $resource, $updatedResource): void
    {
        // update resource
        $resource->setTitle($updatedResource->getTitle());
        $resource->setContent($updatedResource->getContent());
        $resource->setLink($updatedResource->getLink());
        $resource->setVisibility($updatedResource->getVisibility());
        $resource->setIsPublished($updatedResource->isIsPublished());
        $resource->setRelation($updatedResource->getRelation());
        $resource->updateCategories($updatedResource->getCategories());
        // check for errors
        $this->serializerService->checkErrors($resource);
        // save
        $this->resourceRepository->save($resource, true);
    }

    public function updateMedia(Resource $resource, $media): void
    {
        if ($media) {
            // check and upload media
            $resource->setMedia(
                $this->fileUploaderService->uploadMedia(
                    $media,
                    $resource->getId(),
                    $this->params->get("app.resource.media.path")
                )
            );
        } else {
            // delete file from server if exists
            $mediaName = $resource->getMedia();
            if ($mediaName) {
                $mediaPath = $this->params->get("app.resource.media.path") . '/' . $mediaName;
                $this->fileUploaderService->deleteFile($mediaPath);
            }
            $resource->setMedia(null);
        }
        // save
        $this->resourceRepository->save($resource, true);
    }

    public function isLiked(Resource $resource, $user): bool
    {
        // check if user already liked
        $like = $resource->getLikes()->filter(function ($like) use ($user) {
            return $like->getUser() === $user;
        })->first();
        return $like != null;
    }

    public function like(Resource $resource, $user): string
    {
        // check if user already liked
        $like = $resource->getLikes()->filter(function ($like) use ($user) {
            return $like->getUser() === $user;
        })->first();
        if ($like) {
            $resource->removeLike($like);
            $this->entityManager->remove($like);
            $message = $this->translator->trans('message.resource.unlike_success');
        } else {
            $like = new ResourceLike();
            $like->setUser($user);
            $resource->addLike($like);
            $this->entityManager->persist($like);
            $message = $this->translator->trans('message.resource.like_success');
        }
        // save
        $this->resourceRepository->save($resource, true);
        // return message
        return $message;
    }

    public function isShared(Resource $resource, $user): bool
    {
        // check if user already shared
        $share = $resource->getShares()->filter(function ($share) use ($user) {
            return $share->getUser() === $user;
        })->first();
        return $share != null;
    }

    public function share(Resource $resource, $user): string
    {
        // check if user already shared
        $share = $resource->getShares()->filter(function ($share) use ($user) {
            return $share->getUser() === $user;
        })->first();
        if ($share) {
            $resource->removeShare($share);
            $this->entityManager->remove($share);
            $message = $this->translator->trans('message.resource.unshare_success');
        } else {
            $share = new ResourceShare();
            $share->setUser($user);
            $resource->addShare($share);
            $this->entityManager->persist($share);
            $message = $this->translator->trans('message.resource.share_success');
        }
        // save
        $this->resourceRepository->save($resource, true);
        // return message
        return $message;
    }

    public function isExploited(Resource $resource, $user): bool
    {
        // check if user already exploited
        $exploit = $resource->getExploits()->filter(function ($exploit) use ($user) {
            return $exploit->getUser() === $user;
        })->first();
        return $exploit != null;
    }

    public function exploit(Resource $resource, $user): string
    {
        // check if user already exploited
        $exploit = $resource->getExploits()->filter(function ($exploit) use ($user) {
            return $exploit->getUser() === $user;
        })->first();
        if ($exploit) {
            $resource->removeExploit($exploit);
            $this->entityManager->remove($exploit);
            $message = $this->translator->trans('message.resource.unexploit_success');
        } else {
            $exploit = new ResourceExploit();
            $exploit->setUser($user);
            $resource->addExploit($exploit);
            $this->entityManager->persist($exploit);
            $message = $this->translator->trans('message.resource.exploit_success');
        }
        // save
        $this->resourceRepository->save($resource, true);
        // return message
        return $message;
    }

    public function isSaved(Resource $resource, $user): bool
    {
        // check if user already saved
        $save = $resource->getSaves()->filter(function ($save) use ($user) {
            return $save->getUser() === $user;
        })->first();
        return $save != null;
    }

    public function save(Resource $resource, $user): string
    {
        // check if user already saved
        $save = $resource->getSaves()->filter(function ($save) use ($user) {
            return $save->getUser() === $user;
        })->first();
        if ($save) {
            $resource->removeSave($save);
            $this->entityManager->remove($save);
            $message = $this->translator->trans('message.resource.unsave_success');
        } else {
            $save = new ResourceSave();
            $save->setUser($user);
            $resource->addSave($save);
            $this->entityManager->persist($save);
            $message = $this->translator->trans('message.resource.save_success');
        }
        // save
        $this->resourceRepository->save($resource, true);
        // return message
        return $message;
    }

    public function isConsulted(Resource $resource, $user): bool
    {
        // check if user already consulted
        $consult = $resource->getConsults()->filter(function ($consult) use ($user) {
            return $consult->getUser() === $user;
        })->first();
        return $consult != null;
    }

    public function consult(Resource $resource, $user): string
    {
        // add consult  if latest consult is not today
        $lastConsultation = $resource->getConsults()->filter(function ($consult) use ($user) {
            return $consult->getUser() === $user;
        })->last();
        if (!$lastConsultation || $lastConsultation->getCreatedAt()->format('Y-m-d') != (new \DateTime())->format('Y-m-d')) {
            $consult = new ResourceConsult();
            $consult->setUser($user);
            $resource->addConsult($consult);
            $this->entityManager->persist($consult);
            $message = $this->translator->trans('message.resource.consult_success');
        } else {
            $message = $this->translator->trans('message.resource.consult_error');
        }
        // save
        $this->resourceRepository->save($resource, true);
        // return message
        return $message;
    }

    public function addSharedTo(Resource $resource, $users): int
    {
        $count = 0;
        foreach ($users["users"] as $user) {
            $user = $this->entityManager->getRepository(User::class)->find($user['id']);
            if ($user) {
                // check if user already shared
                $share = $resource->getSharesTo()->filter(function ($share) use ($user) {
                    return $share->getUser() === $user;
                })->first();
                if (!$share) {
                    $share = new ResourceSharedTo();
                    $share->setUser($user);
                    $resource->addSharedTo($share);
                    $this->entityManager->persist($share);
                    $count++;
                }
            } else {
                throw new HttpException(Response::HTTP_NOT_FOUND, $this->translator->trans('message.resource.shared_error'));
            }
        }
        // save
        $this->resourceRepository->save($resource, true);
        // return count
        return $count;
    }

    public function removeSharedTo(Resource $resource, $users): int
    {
        // for each user
        $count = 0;
        foreach ($users["users"] as $user) {
            $user = $this->userRepository->find($user['id']);
            if ($user) {
                // check if user already shared
                $share = $resource->getSharesTo()->filter(function ($share) use ($user) {
                    return $share->getUser() === $user;
                })->first();
                if ($share) {
                    $resource->removeSharedTo($share);
                    $this->entityManager->remove($share);
                    $count++;
                }
            } else {
                throw new HttpException(Response::HTTP_NOT_FOUND, $this->translator->trans('message.resource.shared_error'));
            }
        }
        // save
        $this->resourceRepository->save($resource, true);
        // return count
        return $count;
    }

    public function generateStats(Resource $resource): void
    {
        //        // check if stats already exist today
//        $stats = $resource->getStats()->filter(function ($stat) {
//            return $stat->getCreatedAt()->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
//        })->first();
//        if ($stats) {
//            return new JsonResponse(
//                ['message' => 'Les statistiques de cette ressource ont déjà été générées aujourd\'hui'],
//                Response::HTTP_OK
//            );
//        }
        // get all the stats
        $consults = $resource->getConsults()->count();
        $exploits = $resource->getExploits()->count();
        $likes = $resource->getLikes()->count();
        $saves = $resource->getSaves()->count();
        $shares = $resource->getShares()->count();
        // add to db
        $resourceStats = new ResourceStats();
        $resourceStats->setResource($resource);
        $resourceStats->setNbConsults($consults);
        $resourceStats->setNbExploits($exploits);
        $resourceStats->setNbLikes($likes);
        $resourceStats->setNbSaves($saves);
        $resourceStats->setNbShares($shares);
        // save
        $this->resourceStatsRepository->save($resourceStats);
    }
}