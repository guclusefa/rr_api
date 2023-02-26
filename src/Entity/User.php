<?php

namespace App\Entity;

use App\Entity\Trait\UserTimeStampTrait;
use App\Repository\UserRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'])]
#[UniqueEntity(fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use UserTimeStampTrait;

    const GROUP_GET = ['user:read', 'state:read'];
    const GROUP_ITEM = ['user:read', 'user:item', 'state:read'];
    const GROUP_ITEM_CONFIDENTIAL = ['user:read', 'user:item', 'user:confidential', 'state:read'];
    const GROUP_WRITE = ['user:write'];
    const GROUP_UPDATE = ['user:update'];
    const GROUP_UPDATE_PASSWORD = ['user:update_password'];
    const GROUP_UPDATE_EMAIL = ['user:update_email'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:identifier'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    #[Assert\Email]
    #[Groups(['user:confidential', 'user:write', 'user:update_email'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 4096)]
    #[Groups(['user:write', 'user:update_password'])]
    private ?string $password = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 20)]
    #[Groups(['user:read', 'user:identifier', 'user:write', 'user:update'])]
    private ?string $username = null;

    #[ORM\Column]
    #[Groups(['user:item'])]
    private array $roles = [];

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(min: 3, max: 20)]
    #[Groups(['user:read', 'user:write', 'user:update'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(min: 3, max: 20)]
    #[Groups(['user:read', 'user:write', 'user:update'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 1, nullable: true)]
    #[Assert\Length(min: 1, max: 1)]
    #[Assert\Choice(choices: ['M', 'F', 'O'])]
    #[Groups(['user:read', 'user:update'])]
    private ?string $gender = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\LessThan('-18 years')]
    #[Groups(['user:item', 'user:update'])]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['user:item', 'user:update'])]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $photo = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:identifier'])]
    private ?bool $isCertified = false;

    #[ORM\Column]
    #[Groups(['user:item'])]
    private ?bool $isVerified = false;

    #[ORM\ManyToOne(targetEntity: State::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[AppAssert\ValidState]
    #[Groups(['user:read', 'user:update'])]
    private ?State $state = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Resource::class)]
    private Collection $resources;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResourceLike::class, orphanRemoval: true)]
    private Collection $likes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResourceShare::class, orphanRemoval: true)]
    private Collection $shares;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResourceExploit::class, orphanRemoval: true)]
    private Collection $exploits;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResourceSave::class, orphanRemoval: true)]
    private Collection $saves;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResourceConsult::class, orphanRemoval: true)]
    private Collection $consults;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ResourceSharedTo::class, orphanRemoval: true)]
    private Collection $sharesTo;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserBan::class, orphanRemoval: true)]
    private Collection $bans;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: UserBan::class, orphanRemoval: true)]
    private Collection $bannings;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->shares = new ArrayCollection();
        $this->exploits = new ArrayCollection();
        $this->saves = new ArrayCollection();
        $this->consults = new ArrayCollection();
        $this->sharesTo = new ArrayCollection();
        $this->bans = new ArrayCollection();
        $this->bannings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

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

    public function isIsCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function setIsCertified(bool $isCertified): self
    {
        $this->isCertified = $isCertified;

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

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

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
            $resource->setAuthor($this);
        }

        return $this;
    }

    public function removeResource(Resource $resource): self
    {
        if ($this->resources->removeElement($resource)) {
            // set the owning side to null (unless already changed)
            if ($resource->getAuthor() === $this) {
                $resource->setAuthor(null);
            }
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
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
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
            $like->setUser($this);
        }

        return $this;
    }

    public function removeLike(ResourceLike $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
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
            $share->setUser($this);
        }

        return $this;
    }

    public function removeShare(ResourceShare $share): self
    {
        if ($this->shares->removeElement($share)) {
            // set the owning side to null (unless already changed)
            if ($share->getUser() === $this) {
                $share->setUser(null);
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
            $exploit->setUser($this);
        }

        return $this;
    }

    public function removeExploit(ResourceExploit $exploit): self
    {
        if ($this->exploits->removeElement($exploit)) {
            // set the owning side to null (unless already changed)
            if ($exploit->getUser() === $this) {
                $exploit->setUser(null);
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
            $save->setUser($this);
        }

        return $this;
    }

    public function removeSave(ResourceSave $save): self
    {
        if ($this->saves->removeElement($save)) {
            // set the owning side to null (unless already changed)
            if ($save->getUser() === $this) {
                $save->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceConsult>
     */
    public function getConsults(): Collection
    {
        return $this->consults;
    }

    public function addConsult(ResourceConsult $consult): self
    {
        if (!$this->consults->contains($consult)) {
            $this->consults->add($consult);
            $consult->setUser($this);
        }

        return $this;
    }

    public function removeConsult(ResourceConsult $consult): self
    {
        if ($this->consults->removeElement($consult)) {
            // set the owning side to null (unless already changed)
            if ($consult->getUser() === $this) {
                $consult->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ResourceSharedTo>
     */
    public function getSharesTo(): Collection
    {
        return $this->sharesTo;
    }

    public function addSharesTo(ResourceSharedTo $sharesTo): self
    {
        if (!$this->sharesTo->contains($sharesTo)) {
            $this->sharesTo->add($sharesTo);
            $sharesTo->setUser($this);
        }

        return $this;
    }

    public function removeSharesTo(ResourceSharedTo $sharesTo): self
    {
        if ($this->sharesTo->removeElement($sharesTo)) {
            // set the owning side to null (unless already changed)
            if ($sharesTo->getUser() === $this) {
                $sharesTo->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserBan>
     */
    public function getBans(): Collection
    {
        return $this->bans;
    }

    public function addBan(UserBan $ban): self
    {
        if (!$this->bans->contains($ban)) {
            $this->bans->add($ban);
            $ban->setUser($this);
        }

        return $this;
    }

    public function removeBan(UserBan $ban): self
    {
        if ($this->bans->removeElement($ban)) {
            // set the owning side to null (unless already changed)
            if ($ban->getUser() === $this) {
                $ban->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserBan>
     */
    public function getBannings(): Collection
    {
        return $this->bannings;
    }

    public function addBanning(UserBan $banning): self
    {
        if (!$this->bannings->contains($banning)) {
            $this->bannings->add($banning);
            $banning->setAuthor($this);
        }

        return $this;
    }

    public function removeBanning(UserBan $banning): self
    {
        if ($this->bannings->removeElement($banning)) {
            // set the owning side to null (unless already changed)
            if ($banning->getAuthor() === $this) {
                $banning->setAuthor(null);
            }
        }

        return $this;
    }

    // TODO : a revoir
    #[VirtualProperty]
    #[SerializedName('photo')]
    #[Groups(['user:read', 'user:identifier'])]
    public function getFullPhotoPath(): ?string
    {
        if (null === $this->photo) return null;
        return "http://localhost:8000/" . 'uploads/users/images/' . $this->photo;
    }
}
