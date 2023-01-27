<?php

namespace App\Entity;

use App\Entity\Trait\TimeStampTrait;
use App\Repository\ResourceStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResourceStatsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ResourceStats
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stats')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Resource $resource = null;

    #[ORM\Column]
    private ?int $nbConsults = null;

    #[ORM\Column]
    private ?int $nbExploits = null;

    #[ORM\Column]
    private ?int $nbLikes = null;

    #[ORM\Column]
    private ?int $nbSaves = null;

    #[ORM\Column]
    private ?int $nbShares = null;

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
}
