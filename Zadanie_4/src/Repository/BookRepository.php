<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * BookRepository предоставляет методы для работы с книгами в базе данных.
 * Реализует запросы для получения и фильтрации книг.
 */
class BookRepository extends ServiceEntityRepository
{
    /**
     * Конструктор репозитория.
     * Инициализирует репозиторий для работы с сущностью Book.
     * 
     * @param ManagerRegistry $doctrineRegistry Реестр менеджеров Doctrine
     */
    public function __construct(ManagerRegistry $doctrineRegistry)
    {
        parent::__construct($doctrineRegistry, Book::class);
    }

    /**
     * Получает все книги, отсортированные по дате прочтения в порядке убывания.
     * 
     * @return array Массив книг
     */
    public function fetchAllSortedByCompletionDateDescending(): array
    {
        return $this->createQueryBuilder("library_item")
            ->orderBy("library_item.completionDate", "DESC")
            ->getQuery()
            ->getResult();
    }

    /**
     * Получает книги конкретного владельца, отсортированные по дате прочтения в порядке убывания.
     * 
     * @param int $ownerIdentifier Идентификатор владельца книг
     * @return array Массив книг владельца
     */
    public function fetchByOwnerSortedByCompletionDateDescending(int $ownerIdentifier): array
    {
        return $this->createQueryBuilder("library_item")
            ->andWhere("library_item.ownerUser = :owner_id")
            ->setParameter("owner_id", $ownerIdentifier)
            ->orderBy("library_item.completionDate", "DESC")
            ->getQuery()
            ->getResult();
    }
}
