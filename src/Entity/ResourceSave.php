<?php

namespace App\Entity;

use App\Entity\Trait\ResourceSaveTimeStampTrait;
use App\Repository\ResourceSaveRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ResourceSaveRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResourceSave
{
    const GROUP_GET = ['resourceSave:read', 'resource:identifier', 'user:identifier'];

    use ResourceSaveTimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resourceSave:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'saves')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resourceSave:read'])]
    private ?Resource $resource = null;

    #[ORM\ManyToOne(inversedBy: 'saves')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resourceSave:read'])]
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
