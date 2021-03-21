<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\SuggestionRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Suggestion
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"suggestion"})
     */
    private UuidInterface $id;
    /**
     * @ORM\Column(type="string", length=180)
     * @Groups("suggestion")
     * @Assert\NotBlank(groups={"suggestion", "edit"})
     */
    private ?string $category = null;
    /**
     * @ORM\Column(type="string", length=180)
     * @Groups("suggestion")
     * @Assert\NotBlank(groups={"suggestion", "edit"})
     */
    private ?string $name = null;
    /**
     * @ORM\Column(type="string")
     * @Groups("suggestion")
     * @Assert\NotBlank(groups={"suggestion", "edit"})
     */
    private ?string $description = null;
    /**
     * @ORM\Column(type="boolean")
     * @Groups("suggestion")
     */
    private bool $deprecated = false;
    /**
     * @ORM\Column(type="string")
     * @Groups("suggestion")
     * @Assert\NotBlank(groups={"suggestion", "edit"})
     */
    private ?string $status = 'new';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="suggestions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("user")
     */
    private ?Users $user = null;

    /**
     * @var Collection|Vote[]
     * @ORM\OneToMany(targetEntity="App\Entity\Vote", mappedBy="suggestion", orphanRemoval=true, cascade={"PERSIST"})
     * @Groups("vote")
     */
    private Collection $votes;

    /**
     * Suggestion constructor.
     */
    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return (string) $this->id;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     * @return Suggestion
     */
    public function setCategory(?string $category): Suggestion
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Suggestion
     */
    public function setName(?string $name): Suggestion
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Suggestion
     */
    public function setDescription(?string $description): Suggestion
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * @param bool $deprecated
     * @return Suggestion
     */
    public function setDeprecated(bool $deprecated): Suggestion
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return Suggestion
     */
    public function setStatus(?string $status): Suggestion
    {
        $this->status = $status;
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
     * @return Suggestion
     */
    public function setUser(?Users $user): Suggestion
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Vote[]|Collection
     */
    public function getVotes(): Collection|array
    {
        return $this->votes;
    }

    /**
     * @param Vote[]|Collection $votes
     * @return Suggestion
     */
    public function setVotes(Collection|array $votes): Suggestion
    {
        $this->votes = $votes;
        return $this;
    }

    /**
     * @Groups("suggestion")
     */
    public function getVotesUp(): int
    {
        return $this->votes->filter(function (Vote $vote):bool {
            return $vote->isVoteValue();
        })->count();
    }

    /**
     * @Groups("suggestion")
     */
    public function getVotesDown(): int
    {
        return $this->votes->filter(function (Vote $vote):bool {
            return !$vote->isVoteValue();
        })->count();
    }
}
