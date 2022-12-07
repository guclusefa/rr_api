<?php

namespace App\Entity;

use App\Repository\RessourceStatsRepository;
use App\Trait\TimeStampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RessourceStatsRepository::class)]
class RessourceStats
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $consultations = null;

    #[ORM\Column]
    private ?int $exploitations = null;

    #[ORM\Column]
    private ?int $favourites = null;

    #[ORM\Column]
    private ?int $shares = null;

    #[ORM\ManyToOne(inversedBy: 'ressourceStats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Resource $resource = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConsultations(): ?int
    {
        return $this->consultations;
    }

    public function setConsultations(int $consultations): self
    {
        $this->consultations = $consultations;

        return $this;
    }

    public function getExploitations(): ?int
    {
        return $this->exploitations;
    }

    public function setExploitations(int $exploitations): self
    {
        $this->exploitations = $exploitations;

        return $this;
    }

    public function getFavourites(): ?int
    {
        return $this->favourites;
    }

    public function setFavourites(int $favourites): self
    {
        $this->favourites = $favourites;

        return $this;
    }

    public function getShares(): ?int
    {
        return $this->shares;
    }

    public function setShares(int $shares): self
    {
        $this->shares = $shares;

        return $this;
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
}
