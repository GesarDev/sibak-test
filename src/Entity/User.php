<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'])]
#[ORM\Table(name: 'users')]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\+?[0-9\-\s()]{7,30}$/')]
    private ?string $phone = null;

    #[Ignore]
    #[ORM\Column(name: 'password', length: 255)]
    private string $passwordHash;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, FeedbackMessage>
     */
    #[ORM\OneToMany(targetEntity: FeedbackMessage::class, mappedBy: 'user')]
    private Collection $feedbackMessages;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->feedbackMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, FeedbackMessage>
     */
    public function getFeedbackMessages(): Collection
    {
        return $this->feedbackMessages;
    }

    public function addFeedbackMessage(FeedbackMessage $feedbackMessage): static
    {
        if (!$this->feedbackMessages->contains($feedbackMessage)) {
            $this->feedbackMessages->add($feedbackMessage);
            $feedbackMessage->setUser($this);
        }

        return $this;
    }

    public function removeFeedbackMessage(FeedbackMessage $feedbackMessage): static
    {
        if ($this->feedbackMessages->removeElement($feedbackMessage)) {
            if ($feedbackMessage->getUser() === $this) {
                $feedbackMessage->setUser(null);
            }
        }

        return $this;
    }
}
