<?php

namespace App\Entity;

use App\Entity\Trait\RelationTimeStampTrait;
use App\Repository\RelationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RelationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Relation
{
    use RelationTimeStampTrait;

    const GROUP_GET = ['relation:read'];
    const GROUP_ITEM = ['relation:read', 'relation:item'];
    const GROUP_WRITE = ['relation:write'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['relation:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Groups(['relation:read', 'relation:write'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'relation', targetEntity: Resource::class)]
    #[Groups(['relation:item'])]
    private Collection $resources;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(Resource $resource): self
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
            $resource->setRelation($this);
        }

        return $this;
    }

    public function removeResource(Resource $resource): self
    {
        if ($this->resources->removeElement($resource)) {
            // set the owning side to null (unless already changed)
            if ($resource->getRelation() === $this) {
                $resource->setRelation(null);
            }
        }

        return $this;
    }
}
