<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Trait\TimeStampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'])]
#[UniqueEntity(fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    #[Groups(['user:read'])]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $mobile = null;

    #[ORM\Column(length: 1, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $gender = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $bio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $photo = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?bool $isVerified = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?bool $isBanned = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?State $state = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Resource::class, orphanRemoval: true)]
    private Collection $resources;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: Resource::class, mappedBy: 'sharedTo')]
    private Collection $sharedResources;

    #[ORM\ManyToMany(targetEntity: Resource::class, inversedBy: 'favourites')]
    #[ORM\JoinTable(name: 'user_favourite_resource')]
    private Collection $favourites;

    #[ORM\ManyToMany(targetEntity: Resource::class, inversedBy: 'saves')]
    #[ORM\JoinTable(name: 'user_saved_resource')]
    private Collection $saves;

    #[ORM\ManyToMany(targetEntity: Resource::class, inversedBy: 'exploits')]
    #[ORM\JoinTable(name: 'user_exploited_resource')]
    private Collection $exploits;

    #[ORM\ManyToMany(targetEntity: Resource::class, inversedBy: 'shares')]
    #[ORM\JoinTable(name: 'user_shared_resource')]
    private Collection $shared;

    #[ORM\ManyToMany(targetEntity: Resource::class, inversedBy: 'consults')]
    #[ORM\JoinTable(name: 'user_consulted_resource')]
    private Collection $consults;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->sharedResources = new ArrayCollection();
        $this->favourites = new ArrayCollection();
        $this->saves = new ArrayCollection();
        $this->exploits = new ArrayCollection();
        $this->shared = new ArrayCollection();
        $this->consults = new ArrayCollection();
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

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

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

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

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

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

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

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

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

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

    public function isIsBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): self
    {
        $this->isBanned = $isBanned;

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
     * @return Collection<int, Resource>
     */
    public function getSharedResources(): Collection
    {
        return $this->sharedResources;
    }

    public function addSharedResource(Resource $sharedResource): self
    {
        if (!$this->sharedResources->contains($sharedResource)) {
            $this->sharedResources->add($sharedResource);
            $sharedResource->addSharedTo($this);
        }

        return $this;
    }

    public function removeSharedResource(Resource $sharedResource): self
    {
        if ($this->sharedResources->removeElement($sharedResource)) {
            $sharedResource->removeSharedTo($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getFavourites(): Collection
    {
        return $this->favourites;
    }

    public function addFavourite(Resource $favourite): self
    {
        if (!$this->favourites->contains($favourite)) {
            $this->favourites->add($favourite);
        }

        return $this;
    }

    public function removeFavourite(Resource $favourite): self
    {
        $this->favourites->removeElement($favourite);

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getSaves(): Collection
    {
        return $this->saves;
    }

    public function addSave(Resource $save): self
    {
        if (!$this->saves->contains($save)) {
            $this->saves->add($save);
        }

        return $this;
    }

    public function removeSave(Resource $save): self
    {
        $this->saves->removeElement($save);

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getExploits(): Collection
    {
        return $this->exploits;
    }

    public function addExploit(Resource $exploit): self
    {
        if (!$this->exploits->contains($exploit)) {
            $this->exploits->add($exploit);
        }

        return $this;
    }

    public function removeExploit(Resource $exploit): self
    {
        $this->exploits->removeElement($exploit);

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getShared(): Collection
    {
        return $this->shared;
    }

    public function addShared(Resource $shared): self
    {
        if (!$this->shared->contains($shared)) {
            $this->shared->add($shared);
        }

        return $this;
    }

    public function removeShared(Resource $shared): self
    {
        $this->shared->removeElement($shared);

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getConsults(): Collection
    {
        return $this->consults;
    }

    public function addConsult(Resource $consult): self
    {
        if (!$this->consults->contains($consult)) {
            $this->consults->add($consult);
        }

        return $this;
    }

    public function removeConsult(Resource $consult): self
    {
        $this->consults->removeElement($consult);

        return $this;
    }
}
