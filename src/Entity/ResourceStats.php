<?php

namespace App\Entity;

use App\Entity\Trait\ResourceStatsTimeStampTrait;
use App\Repository\ResourceStatsRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ResourceStatsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResourceStats
{
    const GROUP_GET = ['resourceStats:read'];

    use ResourceStatsTimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resourceStats:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stats')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resourceStats:item'])]
    private ?Resource $resource = null;

    #[ORM\Column]
    #[Groups(['resourceStats:read'])]
    private ?int $nbConsults = null;

    #[ORM\Column]
    #[Groups(['resourceStats:read'])]
    private ?int $nbExploits = null;

    #[ORM\Column]
    #[Groups(['resourceStats:read'])]
    private ?int $nbLikes = null;

    #[ORM\Column]
    #[Groups(['resourceStats:read'])]
    private ?int $nbSaves = null;

    #[ORM\Column]
    #[Groups(['resourceStats:read'])]
    private ?int $nbShares = null;

    #[ORM\Column]
    #[Groups(['resourceStats:read'])]
    private ?int $nbComments = null;

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

    public function getNbConsults(): ?int
    {
        return $this->nbConsults;
    }

    public function setNbConsults(int $nbConsults): self
    {
        $this->nbConsults = $nbConsults;

        return $this;
    }

    public function getNbExploits(): ?int
    {
        return $this->nbExploits;
    }

    public function setNbExploits(int $nbExploits): self
    {
        $this->nbExploits = $nbExploits;

        return $this;
    }

    public function getNbLikes(): ?int
    {
        return $this->nbLikes;
    }

    public function setNbLikes(int $nbLikes): self
    {
        $this->nbLikes = $nbLikes;

        return $this;
    }

    public function getNbSaves(): ?int
    {
        return $this->nbSaves;
    }

    public function setNbSaves(int $nbSaves): self
    {
        $this->nbSaves = $nbSaves;

        return $this;
    }

    public function getNbShares(): ?int
    {
        return $this->nbShares;
    }

    public function setNbShares(int $nbShares): self
    {
        $this->nbShares = $nbShares;

        return $this;
    }

    public function getNbComments(): ?int
    {
        return $this->nbComments;
    }

    public function setNbComments(int $nbComments): self
    {
        $this->nbComments = $nbComments;

        return $this;
    }
}
