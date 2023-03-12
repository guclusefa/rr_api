<?php

namespace App\Entity;

use App\Entity\Trait\StateTimeStampTrait;
use App\Repository\StateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StateRepository::class)]
#[ORM\HasLifecycleCallbacks]
class State
{
    use StateTimeStampTrait;

    const GROUP_GET = ['state:read'];
    const GROUP_ITEM = ['state:read', 'state:item'];
    const GROUP_WRITE = ['state:write'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['state:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Groups(['state:read', 'state:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Groups(['state:read', 'state:write'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'state', targetEntity: User::class, cascade: ['persist'])]
    #[Groups(['state:item'])]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setState($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getState() === $this) {
                $user->setState(null);
            }
        }

        return $this;
    }
}
