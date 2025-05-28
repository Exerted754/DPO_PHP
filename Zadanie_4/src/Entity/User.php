<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM_Mapping;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface as PasswordAuthUserIface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserIface;
use Doctrine\DBAL\Types\Types as DoctrineTypes;

/**
 * Сущность User представляет пользователя системы.
 * Реализует интерфейсы безопасности Symfony для аутентификации и авторизации.
 */
#[ORM_Mapping\Entity(repositoryClass: UserRepository::class)]
#[ORM_Mapping\Table(name: "app_users")]
#[UniqueEntity(fields: ["accountEmail"], message: "An account with this email address already exists.")]
class User implements SecurityUserIface, PasswordAuthUserIface
{
    /**
     * Уникальный идентификатор пользователя
     */
    #[ORM_Mapping\Id]
    #[ORM_Mapping\GeneratedValue]
    #[ORM_Mapping\Column(name: "identifier", type: DoctrineTypes::INTEGER)]
    private ?int $identifier = null;

    /**
     * Email пользователя (уникальный)
     */
    #[ORM_Mapping\Column(name: "account_email", length: 180, unique: true)]
    private ?string $accountEmail = null;

    /**
     * Роли пользователя в системе
     */
    #[ORM_Mapping\Column(name: "security_roles", type: DoctrineTypes::JSON)]
    private array $securityRoles = [];

    /**
     * Хеш пароля пользователя
     */
    #[ORM_Mapping\Column(name: "hashed_password")]
    private ?string $hashedPassword = null;

    /**
     * Дата регистрации пользователя
     */
    #[ORM_Mapping\Column(name: "registration_date", type: DoctrineTypes::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $registrationDate = null;

    /**
     * Конструктор класса.
     * Устанавливает текущую дату как дату регистрации пользователя.
     */
    public function __construct()
    {
        $this->registrationDate = new \DateTime();
    }

    /**
     * Получает идентификатор пользователя
     */
    public function getIdentifier(): ?int
    {
        return $this->identifier;
    }

    /**
     * Получает идентификатор пользователя (алиас для getIdentifier)
     */
    public function getId(): ?int
    {
        return $this->identifier;
    }

    /**
     * Получает email пользователя
     */
    public function getAccountEmail(): ?string
    {
        return $this->accountEmail;
    }

    /**
     * Устанавливает email пользователя
     */
    public function setAccountEmail(string $email): static
    {
        $this->accountEmail = $email;
        return $this;
    }

    /**
     * Получает email пользователя (алиас для getAccountEmail)
     */
    public function getEmail(): ?string
    {
        return $this->accountEmail;
    }

    /**
     * Устанавливает email пользователя (алиас для setAccountEmail)
     */
    public function setEmail(string $email): static
    {
        $this->accountEmail = $email;
        return $this;
    }

    /**
     * Получает идентификатор пользователя для аутентификации
     * Реализует метод интерфейса UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->accountEmail;
    }

    /**
     * Получает роли пользователя
     * Реализует метод интерфейса UserInterface
     */
    public function getRoles(): array
    {
        $currentRoles = $this->securityRoles;
        $currentRoles[] = "ROLE_USER";
        return array_unique($currentRoles);
    }

    /**
     * Устанавливает роли пользователя
     */
    public function setRoles(array $roles): static
    {
        $this->securityRoles = $roles;
        return $this;
    }

    /**
     * Получает хеш пароля пользователя
     * Реализует метод интерфейса PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->hashedPassword;
    }

    /**
     * Устанавливает хеш пароля пользователя
     */
    public function setPassword(string $password): static
    {
        $this->hashedPassword = $password;
        return $this;
    }

    /**
     * Очищает чувствительные данные пользователя
     * Реализует метод интерфейса UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * Получает дату регистрации пользователя
     */
    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    /**
     * Устанавливает дату регистрации пользователя
     */
    public function setRegistrationDate(\DateTimeInterface $date): static
    {
        $this->registrationDate = $date;
        return $this;
    }

    /**
     * Получает дату создания пользователя (алиас для getRegistrationDate)
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    /**
     * Устанавливает дату создания пользователя (алиас для setRegistrationDate)
     */
    public function setCreatedAt(\DateTimeInterface $date): static
    {
        $this->registrationDate = $date;
        return $this;
    }
}
