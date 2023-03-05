<?php

namespace App\Entity;

use App\Entity\Trait\ResourceConsultTimeStampTrait;
use App\Repository\ResourceConsultRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ResourceConsultRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResourceConsult
{
    const GROUP_GET = ['resourceConsult:read', 'resource:identifier', 'user:identifier'];

    use ResourceConsultTimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resourceConsult:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'consults')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resourceConsult:read'])]
    private ?Resource $resource = null;

    #[ORM\ManyToOne(inversedBy: 'consults')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resourceConsult:read'])]
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
