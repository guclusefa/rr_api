<?php

namespace App\Entity;

use App\Entity\Trait\ResourceShareTimeStampTrait;
use App\Repository\ResourceShareRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ResourceShareRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResourceShare
{
    const GROUP_GET = ['resourceShare:read', 'resource:identifier', 'user:identifier'];

    use ResourceShareTimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resourceShare:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'shares')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resourceShare:read'])]
    private ?Resource $resource = null;

    #[ORM\ManyToOne(inversedBy: 'shares')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resourceShare:read'])]
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
