<?php

namespace App\Entity;

use App\Entity\Trait\UserTimeStampTrait;
use App\Repository\UserBanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserBanRepository::class)]
#[ORM\HasLifecycleCallbacks]
class UserBan
{
    use UserTimeStampTrait;

    const GROUP_READ = ['user_ban:read'];
    const GROUP_ITEM = ['user_ban:read','user_ban:item'];
    const GROUP_WRITE = ['user_ban:write'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_ban:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bans')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_ban:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'bannings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_ban:read'])]
    private ?User $author = null;

    #[ORM\Column(length: 255)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['user_ban:read', 'user_ban:write'])]
    private ?string $reason = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThan('now')]
    #[Groups(['user_ban:read', 'user_ban:write'])]
    private ?\DateTimeInterface $endDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }
}
