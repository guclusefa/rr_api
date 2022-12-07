<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use App\Trait\TimeStampTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    private ?QuestionType $type = null;

    #[ORM\OneToOne(mappedBy: 'question', cascade: ['persist', 'remove'])]
    private ?QuestionAnswer $questionAnswer = null;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): ?QuestionType
    {
        return $this->type;
    }

    public function setType(?QuestionType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getQuestionAnswer(): ?QuestionAnswer
    {
        return $this->questionAnswer;
    }

    public function setQuestionAnswer(QuestionAnswer $questionAnswer): self
    {
        // set the owning side of the relation if necessary
        if ($questionAnswer->getQuestion() !== $this) {
            $questionAnswer->setQuestion($this);
        }

        $this->questionAnswer = $questionAnswer;

        return $this;
    }
}
