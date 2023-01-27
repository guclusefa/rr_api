<?php

namespace App\Entity;

use App\Entity\Trait\TimeStampTrait;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Category
{
    use TimeStampTrait;

    const GROUP_GET = ['category:read'];
    const GROUP_ITEM = ['category:read', 'category:item'];
    const GROUP_WRITE = ['category:write'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Groups(['category:read', 'category:write'])]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Resource::class, mappedBy: 'categories')]
    #[Groups(['category:item'])]
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
            $resource->addCategory($this);
        }

        return $this;
    }

    public function removeResource(Resource $resource): self
    {
        if ($this->resources->removeElement($resource)) {
            $resource->removeCategory($this);
        }

        return $this;
    }
}
