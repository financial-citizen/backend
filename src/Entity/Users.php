<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JetBrains\PhpStorm\Pure;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @UniqueEntity(fields={"email"}, groups={"register", "edit"})
 * @UniqueEntity(fields={"ahv"}, groups={"register", "edit"})
 * @UniqueEntity(fields={"phone"}, groups={"register", "edit"})
 */
class Users implements UserInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups({"user", "currentUser"})
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups("currentUser")
     * @Assert\NotBlank(groups={"register", "edit"})
     * @Assert\Email(groups={"register", "edit"})
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups("currentUser")
     * @Assert\NotBlank(groups={"register", "edit"})
     */
    private ?string $phone = null;

    /**
     * @ORM\Column(type="string", length=180)
     * @Groups("currentUser", "user")
     * @Assert\NotBlank(groups={"register", "edit"})
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups("currentUser")
     * @Assert\NotBlank(groups={"register"})
     */
    private ?string $ahv = null;

    /**
     * @ORM\Column(type="date")
     * @Groups("currentUser")
     * @Assert\Type("datetime", groups={"register", "edit"})
     */
    private ?DateTime $birthday = null;

    /**
     * @var Collection|Suggestion[]
     * @ORM\OneToMany(targetEntity="App\Entity\Suggestion", mappedBy="user", orphanRemoval=true, cascade={"PERSIST"})
     * @Groups("suggestionRel")
     */
    private Collection $suggestions;

    /**
     * @var Collection|Vote[]
     * @ORM\OneToMany(targetEntity="App\Entity\Vote", mappedBy="user", orphanRemoval=true, cascade={"PERSIST"})
     * @Groups("vote")
     */
    private Collection $votes;

    /**
     * @ORM\Column(type="string")
     * @Groups("user")
     * @Assert\NotBlank(groups={"register", "edit"}))
     * @Assert\Choice({"ROLE_CITIZEN", "ROLE_GOVERNMENT"}, groups={"register", "edit"}))
     */
    private string $roles = 'ROLE_CITIZEN';

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank(groups={"register"})
     */
    private ?string $password = null;

    #[Pure] public function __construct()
    {
        $this->suggestions = new ArrayCollection();
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return (string) $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return Users
     */
    public function setEmail(?string $email): Users
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return Users
     */
    public function setPhone(?string $phone): Users
    {
        $this->phone = $phone;
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
     * @return Users
     */
    public function setName(?string $name): Users
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAhv(): ?string
    {
        return $this->ahv;
    }

    /**
     * @param string|null $ahv
     * @return Users
     */
    public function setAhv(?string $ahv): Users
    {
        $this->ahv = $ahv;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getBirthday(): ?DateTime
    {
        return $this->birthday;
    }

    /**
     * @param DateTime|null $birthday
     * @return Users
     */
    public function setBirthday(?DateTime $birthday): Users
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * @return Suggestion[]|Collection
     */
    public function getSuggestions(): ArrayCollection|Collection|array
    {
        return $this->suggestions;
    }

    /**
     * @param Suggestion[]|Collection $suggestion
     * @return Users
     */
    public function setSuggestions(ArrayCollection|Collection|array $suggestions): Users
    {
        $this->suggestions = $suggestions;
        return $this;
    }

    /**
     * @return Vote[]|Collection
     */
    public function getVotes(): ArrayCollection|Collection|array
    {
        return $this->votes;
    }

    /**
     * @param Vote[]|Collection $votes
     * @return Users
     */
    public function setVotes(ArrayCollection|Collection|array $votes): Users
    {
        $this->votes = $votes;
        return $this;
    }

    /**
     * @return string[]
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return array_unique([$this->roles]);
    }

    public function setRoles(string $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /** @see UserInterface */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /** @see UserInterface */
    public function getSalt(): void
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}
