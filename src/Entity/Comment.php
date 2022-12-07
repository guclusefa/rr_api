<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use App\Trait\TimeStampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Comment
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Relation $resource = null;

    #[ORM\OneToOne(inversedBy: 'comment', cascade: ['persist', 'remove'])]
    private ?User $replyTo = null;

    #[ORM\Column]
    private ?bool $isSuspended = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getResource(): ?Relation
    {
        return $this->resource;
    }

    public function setResource(?Relation $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getReplyTo(): ?User
    {
        return $this->replyTo;
    }

    public function setReplyTo(?User $replyTo): self
    {
        $this->replyTo = $replyTo;

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
}
