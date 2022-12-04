<?php

namespace App\Entity;

use App\Repository\ResourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *          "api_resources_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"resource:read"})
 * )
 *
 * @Hateoas\Relation(
 *     "delete",
 *     href = @Hateoas\Route(
 *          "api_resources_delete",
 *          parameters = { "id" = "expr(object.getId())" },
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"resource:read"}, excludeIf = "expr(not is_granted('ROLE_ADMIN'))")
 * )
 *
 * @Hateoas\Relation(
 *     "update",
 *     href = @Hateoas\Route(
 *          "api_resources_update",
 *          parameters = { "id" = "expr(object.getId())" },
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"resource:read"}, excludeIf = "expr(not is_granted('ROLE_ADMIN'))")
 * )
 */
#[ORM\Entity(repositoryClass: ResourceRepository::class)]
class Resource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['resource:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[Groups(['resource:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[Groups(['resource:read'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'ressources')]
    #[Groups(['resource:read'])]
    private ?User $author = null;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
