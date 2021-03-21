<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=VoteRepository::class)
 */
class Vote
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"vote"})
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("vote")
     */
    private bool $voteValue = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("userLink")
     */
    private ?Users $user = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Suggestion", inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("suggestion")
     */
    private ?Suggestion $suggestion = null;

    public function getId(): ?string
    {
        return (string) $this->id;
    }

    /**
     * @return bool
     */
    public function isVoteValue(): bool
    {
        return $this->voteValue;
    }

    /**
     * @param bool $voteValue
     * @return Vote
     */
    public function setVoteValue(bool $voteValue): Vote
    {
        $this->voteValue = $voteValue;
        return $this;
    }

    /**
     * @return Users|null
     */
    public function getUser(): ?Users
    {
        return $this->user;
    }

    /**
     * @param Users|null $user
     * @return Vote
     */
    public function setUser(?Users $user): Vote
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Suggestion|null
     */
    public function getSuggestion(): ?Suggestion
    {
        return $this->suggestion;
    }

    /**
     * @param Suggestion|null $suggestion
     * @return Vote
     */
    public function setSuggestion(?Suggestion $suggestion): Vote
    {
        $this->suggestion = $suggestion;
        return $this;
    }
}