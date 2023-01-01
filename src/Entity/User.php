<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Trait\UserTimeStampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
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
    const GROUP_GET = ['user:read'];
    const GROUP_ITEM = ['user:read', 'user:item'];
    const GROUP_REGISTER = ['user:register'];
    const GROUP_RESET_PASSWORD = ['user:reset_password'];
    const GROUP_UPDATE = ['user:update'];

    use UserTimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    #[Assert\Email]
    #[Groups(['user:confidential', 'user:register'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 4096)]
    #[Groups(['user:register', 'user:reset_password'])]
    private ?string $password = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 20)]
    #[Groups(['user:read', 'user:register', 'user:update'])]
    private ?string $username = null;

    #[ORM\Column]
    #[Groups(['user:item'])]
    private array $roles = [];

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(min: 3, max: 20)]
    #[Groups(['user:read', 'user:register', 'user:update'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(min: 3, max: 20)]
    #[Groups(['user:read', 'user:register', 'user:update'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 1, nullable: true)]
    #[Assert\Length(min: 1, max: 1)]
    #[Groups(['user:item', 'user:update'])]
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
    #[Groups(['user:read', 'user:update'])]
    private ?string $photo = null;

    #[ORM\Column]
    #[Groups(['user:item'])]
    private ?bool $isVerified = false;

    #[ORM\Column]
    #[Groups(['user:item'])]
    private ?bool $isActive = true;

    #[ORM\Column]
    #[Groups(['user:item'])]
    private ?bool $isBanned = false;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['user:item', 'user:update'])]
    private ?State $state = null;

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

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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
}
