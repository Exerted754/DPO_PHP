<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\DBAL\Types\Types as DoctrineDataTypes;
use Doctrine\ORM\Mapping as ORM_Map;
use Symfony\Component\Validator\Constraints as ValidationAssert;

/**
 * Сущность Book представляет книгу в библиотечной системе.
 * Содержит информацию о книге, включая метаданные, файлы и настройки доступа.
 */
#[ORM_Map\Entity(repositoryClass: BookRepository::class)]
#[ORM_Map\Table(name: "library_items")]
class Book
{
    /**
     * Уникальный идентификатор книги
     */
    #[ORM_Map\Id]
    #[ORM_Map\GeneratedValue]
    #[ORM_Map\Column(name: "item_id", type: DoctrineDataTypes::INTEGER)]
    private ?int $itemId = null;

    /**
     * Владелец книги (пользователь)
     */
    #[ORM_Map\ManyToOne(targetEntity: User::class)]
    #[ORM_Map\JoinColumn(name: "owner_user_id", referencedColumnName: "identifier", nullable: false)]
    private ?User $ownerUser = null;

    /**
     * Название книги
     */
    #[ORM_Map\Column(name: "work_title", length: 255)]
    #[ValidationAssert\NotBlank(message: "The title of the book is mandatory.")]
    private ?string $workTitle = null;

    /**
     * Имя автора книги
     */
    #[ORM_Map\Column(name: "creator_name", length: 255)]
    #[ValidationAssert\NotBlank(message: "The author's name is mandatory.")]
    private ?string $creatorName = null;

    /**
     * Путь к файлу обложки книги
     */
    #[ORM_Map\Column(name: "cover_image_location", length: 512, nullable: true)]
    private ?string $coverImageLocation = null;

    /**
     * Путь к цифровому файлу книги
     */
    #[ORM_Map\Column(name: "digital_file_location", length: 512, nullable: true)]
    private ?string $digitalFileLocation = null;

    /**
     * Дата прочтения книги
     */
    #[ORM_Map\Column(name: "completion_date", type: DoctrineDataTypes::DATE_MUTABLE)]
    #[ValidationAssert\NotNull(message: "The date of reading completion must be specified.")]
    private ?\DateTimeInterface $completionDate = null;

    /**
     * Разрешено ли скачивание книги
     */
    #[ORM_Map\Column(name: "is_download_permitted", type: DoctrineDataTypes::BOOLEAN, options: ["default" => false])]
    private bool $isDownloadPermitted = false;

    /**
     * Дата добавления книги в библиотеку
     */
    #[ORM_Map\Column(name: "date_added_to_library", type: DoctrineDataTypes::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $dateAddedToLibrary = null;

    /**
     * Оригинальное имя файла книги
     */
    #[ORM_Map\Column(name: "source_book_filename", length: 255, nullable: true)]
    private ?string $sourceBookFilename = null;

    /**
     * Оригинальное имя файла обложки
     */
    #[ORM_Map\Column(name: "source_cover_filename", length: 255, nullable: true)]
    private ?string $sourceCoverFilename = null;

    /**
     * Конструктор класса.
     * Устанавливает текущую дату как дату добавления книги в библиотеку.
     */
    public function __construct()
    {
        $this->dateAddedToLibrary = new \DateTime();
    }

    /**
     * Получает идентификатор книги
     */
    public function getItemId(): ?int
    {
        return $this->itemId;
    }

    /**
     * Получает владельца книги
     */
    public function getOwnerUser(): ?User
    {
        return $this->ownerUser;
    }

    /**
     * Устанавливает владельца книги
     */
    public function setOwnerUser(?User $userEntity): static
    {
        $this->ownerUser = $userEntity;
        return $this;
    }

    /**
     * Получает название книги
     */
    public function getWorkTitle(): ?string
    {
        return $this->workTitle;
    }

    /**
     * Устанавливает название книги
     */
    public function setWorkTitle(string $titleValue): static
    {
        $this->workTitle = $titleValue;
        return $this;
    }

    /**
     * Получает имя автора книги
     */
    public function getCreatorName(): ?string
    {
        return $this->creatorName;
    }

    /**
     * Устанавливает имя автора книги
     */
    public function setCreatorName(string $authorName): static
    {
        $this->creatorName = $authorName;
        return $this;
    }

    /**
     * Получает путь к файлу обложки
     */
    public function getCoverImageLocation(): ?string
    {
        return $this->coverImageLocation;
    }

    /**
     * Устанавливает путь к файлу обложки
     */
    public function setCoverImageLocation(?string $pathValue): static
    {
        $this->coverImageLocation = $pathValue;
        return $this;
    }

    /**
     * Получает путь к цифровому файлу книги
     */
    public function getDigitalFileLocation(): ?string
    {
        return $this->digitalFileLocation;
    }

    /**
     * Устанавливает путь к цифровому файлу книги
     */
    public function setDigitalFileLocation(?string $pathValue): static
    {
        $this->digitalFileLocation = $pathValue;
        return $this;
    }

    /**
     * Получает дату прочтения книги
     */
    public function getCompletionDate(): ?\DateTimeInterface
    {
        return $this->completionDate;
    }

    /**
     * Устанавливает дату прочтения книги
     */
    public function setCompletionDate(\DateTimeInterface $dateValue): static
    {
        $this->completionDate = $dateValue;
        return $this;
    }

    /**
     * Проверяет, разрешено ли скачивание книги
     */
    public function isDownloadPermitted(): bool
    {
        return $this->isDownloadPermitted;
    }

    /**
     * Устанавливает разрешение на скачивание книги
     */
    public function setIsDownloadPermitted(bool $permittedStatus): static
    {
        $this->isDownloadPermitted = $permittedStatus;
        return $this;
    }

    /**
     * Получает дату добавления книги в библиотеку
     */
    public function getDateAddedToLibrary(): ?\DateTimeInterface
    {
        return $this->dateAddedToLibrary;
    }

    /**
     * Устанавливает дату добавления книги в библиотеку
     */
    public function setDateAddedToLibrary(\DateTimeInterface $dateTimeValue): static
    {
        $this->dateAddedToLibrary = $dateTimeValue;
        return $this;
    }

    /**
     * Получает оригинальное имя файла книги
     */
    public function getSourceBookFilename(): ?string
    {
        return $this->sourceBookFilename;
    }

    /**
     * Устанавливает оригинальное имя файла книги
     */
    public function setSourceBookFilename(?string $filename): static
    {
        $this->sourceBookFilename = $filename;
        return $this;
    }

    /**
     * Получает оригинальное имя файла обложки
     */
    public function getSourceCoverFilename(): ?string
    {
        return $this->sourceCoverFilename;
    }

    /**
     * Устанавливает оригинальное имя файла обложки
     */
    public function setSourceCoverFilename(?string $filename): static
    {
        $this->sourceCoverFilename = $filename;
        return $this;
    }
}
