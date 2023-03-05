<?php

namespace App\Entity;

use App\Entity\Trait\ResourceSharedToTimeStampTrait;
use App\Repository\ResourceSharedToRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ResourceSharedToRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResourceSharedTo
{
    const GROUP_GET = ['resourceSharedTo:read', 'resource:identifier', 'user:identifier'];

    use ResourceSharedToTimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resourceSharedTo:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sharesTo')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resourceSharedTo:read'])]
    private ?Resource $resource = null;

    #[ORM\ManyToOne(inversedBy: 'sharesTo')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resourceSharedTo:read', 'shareTo:read'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
