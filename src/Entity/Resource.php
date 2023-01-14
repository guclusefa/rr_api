<?php

namespace App\Entity;

use App\Entity\Trait\TimeStampTrait;
use App\Repository\ResourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Resource
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resource:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Groups(['resource:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['resource:read'])]
    private ?string $media = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['resource:read'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['resource:read'])]
    private ?int $visibility = null;

    #[ORM\Column]
    #[Groups(['resource:item'])]
    private ?bool $isPublished = null;

    #[ORM\Column]
    #[Groups(['resource:item'])]
    private ?bool $isVerified = null;

    #[ORM\Column]
    #[Groups(['resource:item'])]
    private ?bool $isSuspended = null;

    #[ORM\ManyToOne(inversedBy: 'resources')]
    #[Groups(['resource:read'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'resources')]
    #[Groups(['resource:read'])]
    private ?Relation $relation = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'resources')]
    #[ORM\JoinTable(name: 'resource_categories')]
    #[Groups(['resource:read'])]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getVisibility(): ?int
    {
        return $this->visibility;
    }

    public function setVisibility(int $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isIsSuspended(): ?bool
    {
        return $this->isSuspended;
    }

    public function setIsSuspended(bool $isSuspended): self
    {
        $this->isSuspended = $isSuspended;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getRelation(): ?Relation
    {
        return $this->relation;
    }

    public function setRelation(?Relation $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
