<?php

namespace App\Entity;

use App\Entity\Trait\ResourceTimeStampTrait;
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
    use ResourceTimeStampTrait;

    const GROUP_GET = ['resource:read', 'category:read', 'relation:read', 'user:identifier'];
    const GROUP_ITEM = ['resource:read', 'resource:item', 'category:read', 'relation:read', 'user:identifier'];
    const GROUP_WRITE = ['resource:write'];
    const GROUP_UPDATE = ['resource:update'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resource:read', 'resource:identifier'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Groups(['resource:read', 'resource:identifier', 'resource:write', 'resource:update'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['resource:read'])]
    private ?string $media = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['resource:read', 'resource:write', 'resource:update'])]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url]
    #[Groups(['resource:item', 'resource:write', 'resource:update'])]
    private ?string $link = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [1, 2, 3])]
    #[Groups(['resource:item', 'resource:write', 'resource:update'])]
    private ?int $visibility = null;

    #[ORM\Column]
    #[Groups(['resource:item', 'resource:write', 'resource:update'])]
    private ?bool $isPublished = true;

    #[ORM\Column]
    #[Groups(['resource:item'])]
    private ?bool $isVerified = false;

    #[ORM\Column]
    #[Groups(['resource:item'])]
    private ?bool $isSuspended = false;

    #[ORM\ManyToOne(inversedBy: 'resources')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['resource:read', 'resource:write'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'resources')]
    #[Groups(['resource:read', 'resource:write', 'resource:update'])]
    private ?Relation $relation = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'resources')]
    #[Groups(['resource:read', 'resource:write', 'resource:update'])]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: 'resource', targetEntity: Comment::class, orphanRemoval: true)]
    #[Groups(['resource:read'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'resource', targetEntity: ResourceLike::class, orphanRemoval: true)]
    #[Groups(['resource:read'])]
    private Collection $likes;

    #[ORM\OneToMany(mappedBy: 'resource', targetEntity: ResourceShare::class, orphanRemoval: true)]
    #[Groups(['resource:read'])]
    private Collection $shares;

    #[ORM\OneToMany(mappedBy: 'resource', targetEntity: ResourceExploit::class, orphanRemoval: true)]
    #[Groups(['resource:read'])]
    private Collection $exploits;

    #[ORM\OneToMany(mappedBy: 'resource', targetEntity: ResourceSave::class, orphanRemoval: true)]
    #[Groups(['resource:read'])]
    private Collection $saves;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->shares = new ArrayCollection();
        $this->exploits = new ArrayCollection();
        $this->saves = new ArrayCollection();
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

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

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

    public function updateCategories(Collection $categories): self
    {
        $this->categories->clear();
        foreach ($categories as $category) {
            $this->addCategory($category);
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setResource($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getResource() === $this) {
                $comment->setResource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceLike>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(ResourceLike $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setResource($this);
        }

        return $this;
    }

    public function removeLike(ResourceLike $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getResource() === $this) {
                $like->setResource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceShare>
     */
    public function getShares(): Collection
    {
        return $this->shares;
    }

    public function addShare(ResourceShare $share): self
    {
        if (!$this->shares->contains($share)) {
            $this->shares->add($share);
            $share->setResource($this);
        }

        return $this;
    }

    public function removeShare(ResourceShare $share): self
    {
        if ($this->shares->removeElement($share)) {
            // set the owning side to null (unless already changed)
            if ($share->getResource() === $this) {
                $share->setResource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceExploit>
     */
    public function getExploits(): Collection
    {
        return $this->exploits;
    }

    public function addExploit(ResourceExploit $exploit): self
    {
        if (!$this->exploits->contains($exploit)) {
            $this->exploits->add($exploit);
            $exploit->setResource($this);
        }

        return $this;
    }

    public function removeExploit(ResourceExploit $exploit): self
    {
        if ($this->exploits->removeElement($exploit)) {
            // set the owning side to null (unless already changed)
            if ($exploit->getResource() === $this) {
                $exploit->setResource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceSave>
     */
    public function getSaves(): Collection
    {
        return $this->saves;
    }

    public function addSave(ResourceSave $save): self
    {
        if (!$this->saves->contains($save)) {
            $this->saves->add($save);
            $save->setResource($this);
        }

        return $this;
    }

    public function removeSave(ResourceSave $save): self
    {
        if ($this->saves->removeElement($save)) {
            // set the owning side to null (unless already changed)
            if ($save->getResource() === $this) {
                $save->setResource(null);
            }
        }

        return $this;
    }
}