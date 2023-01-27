<?php

namespace App\Entity;

use App\Entity\Trait\TimeStampTrait;
use App\Repository\ResourceSaveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceSaveRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResourceSave
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'saves')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Resource $resource = null;

    #[ORM\ManyToOne(inversedBy: 'saves')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
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
