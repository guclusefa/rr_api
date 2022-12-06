<?php

namespace App\Entity;

use App\Repository\ResourceRepository;
use App\Trait\ResourceTimeStampTrait;
use App\Trait\TimeStampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

///**
// * @Hateoas\Relation(
// *     "self",
// *     href = @Hateoas\Route(
// *          "api_resources_show",
// *          parameters = { "id" = "expr(object.getId())" },
// *     ),
// *     exclusion = @Hateoas\Exclusion(groups = {"resource:read"})
// * )
// *
// * @Hateoas\Relation(
// *     "delete",
// *     href = @Hateoas\Route(
// *          "api_resources_delete",
// *          parameters = { "id" = "expr(object.getId())" },
// *     ),
// *     exclusion = @Hateoas\Exclusion(groups = {"resource:read"}, excludeIf = "expr(not is_granted('ROLE_ADMIN'))")
// * )
// *
// * @Hateoas\Relation(
// *     "update",
// *     href = @Hateoas\Route(
// *          "api_resources_update",
// *          parameters = { "id" = "expr(object.getId())" },
// *     ),
// *     exclusion = @Hateoas\Exclusion(groups = {"resource:read"}, excludeIf = "expr(not is_granted('ROLE_ADMIN'))")
// * )
// *
// */
#[ORM\Entity(repositoryClass: ResourceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Resource
{
    use ResourceTimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resource:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['resource:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['resource:read'])]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['resource:read'])]
    private ?string $photo = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['resource:read'])]
    private ?int $visibility = null;

    #[ORM\Column]
    #[Groups(['resource:read'])]
    private ?bool $isPublished = false;

    #[ORM\Column]
    #[Groups(['resource:read'])]
    private ?bool $isVerified = false;

    #[ORM\Column]
    #[Groups(['resource:read'])]
    private ?bool $isSuspended = false;

    #[ORM\ManyToOne(inversedBy: 'resources')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['resource:read'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'resources')]
    #[Groups(['resource:read'])]
    private ?Relation $relation = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'resources')]
    #[Groups(['resource:read'])]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'sharedResources')]
    #[ORM\JoinTable(name: 'resource_shared_user')]
    #[Groups(['resource:read'])]
    private Collection $sharedTo;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favourites')]
    #[Groups(['resource:read'])]
    private Collection $favourites;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'saves')]
    #[Groups(['resource:read'])]
    private Collection $saves;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'exploits')]
    #[Groups(['resource:read'])]
    private Collection $exploits;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'shared')]
    #[Groups(['resource:read'])]
    private Collection $shares;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'consults')]
    #[Groups(['resource:read'])]
    private Collection $consults;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->sharedTo = new ArrayCollection();
        $this->favourites = new ArrayCollection();
        $this->saves = new ArrayCollection();
        $this->exploits = new ArrayCollection();
        $this->shares = new ArrayCollection();
        $this->consults = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
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

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

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
     * @return Collection<int, User>
     */
    public function getSharedTo(): Collection
    {
        return $this->sharedTo;
    }

    public function addSharedTo(User $sharedTo): self
    {
        if (!$this->sharedTo->contains($sharedTo)) {
            $this->sharedTo->add($sharedTo);
        }

        return $this;
    }

    public function removeSharedTo(User $sharedTo): self
    {
        $this->sharedTo->removeElement($sharedTo);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFavourites(): Collection
    {
        return $this->favourites;
    }

    public function addFavourite(User $favourite): self
    {
        if (!$this->favourites->contains($favourite)) {
            $this->favourites->add($favourite);
            $favourite->addFavourite($this);
        }

        return $this;
    }

    public function removeFavourite(User $favourite): self
    {
        if ($this->favourites->removeElement($favourite)) {
            $favourite->removeFavourite($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getSaves(): Collection
    {
        return $this->saves;
    }

    public function addSave(User $save): self
    {
        if (!$this->saves->contains($save)) {
            $this->saves->add($save);
            $save->addSave($this);
        }

        return $this;
    }

    public function removeSave(User $save): self
    {
        if ($this->saves->removeElement($save)) {
            $save->removeSave($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getExploits(): Collection
    {
        return $this->exploits;
    }

    public function addExploit(User $exploit): self
    {
        if (!$this->exploits->contains($exploit)) {
            $this->exploits->add($exploit);
            $exploit->addExploit($this);
        }

        return $this;
    }

    public function removeExploit(User $exploit): self
    {
        if ($this->exploits->removeElement($exploit)) {
            $exploit->removeExploit($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getShares(): Collection
    {
        return $this->shares;
    }

    public function addShare(User $share): self
    {
        if (!$this->shares->contains($share)) {
            $this->shares->add($share);
            $share->addShared($this);
        }

        return $this;
    }

    public function removeShare(User $share): self
    {
        if ($this->shares->removeElement($share)) {
            $share->removeShared($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getConsults(): Collection
    {
        return $this->consults;
    }

    public function addConsult(User $consult): self
    {
        if (!$this->consults->contains($consult)) {
            $this->consults->add($consult);
            $consult->addConsult($this);
        }

        return $this;
    }

    public function removeConsult(User $consult): self
    {
        if ($this->consults->removeElement($consult)) {
            $consult->removeConsult($this);
        }

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }
}
