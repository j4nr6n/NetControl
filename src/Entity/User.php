<?php

namespace App\Entity;

use App\Gravatar\Enum\ImageSet;
use App\Gravatar\Enum\Rating;
use App\Gravatar\Gravatar;
use App\Repository\UserRepository;
use App\Traits\EntityIdTrait;
use App\Traits\TimestampedTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('email')]
#[UniqueEntity('callsign')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use EntityIdTrait;
    use TimestampedTrait;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $name = null;

    #[Assert\Email]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', unique: true)]
    private ?string $email = null;

    #[Assert\Regex(
        pattern: '/^[a-zA-Z]{1,2}\d{1,4}[a-zA-Z]{1,3}$/',
        message: 'This must be a valid callsign'
    )]
    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private ?string $callsign = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $homepageUrl = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $token = null;

    #[ORM\Column(type: 'boolean')]
    private bool $emailVerified = false;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCallsign(): ?string
    {
        return $this->callsign;
    }

    public function setCallsign(?string $callsign): self
    {
        $this->callsign = $callsign;

        return $this;
    }

    public function getHomepageUrl(): ?string
    {
        return $this->homepageUrl;
    }

    public function setHomepageUrl(?string $homepageUrl): self
    {
        $this->homepageUrl = $homepageUrl;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        /** @var string[] $roles */
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $emailVerified): self
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    public function getAvatarUrl(
        int $size = 80,
        ImageSet $imageSet = ImageSet::ROBOHASH,
        Rating $rating = Rating::G
    ): string {
        return Gravatar::getUrl($this->getEmail() ?? '', $size, $imageSet, $rating);
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
