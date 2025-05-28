<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface as PwdAuthUserIface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * UserRepository предоставляет методы для работы с пользователями в базе данных.
 * Реализует интерфейс PasswordUpgraderInterface для обновления паролей пользователей.
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * Конструктор репозитория.
     * Инициализирует репозиторий для работы с сущностью User.
     * 
     * @param ManagerRegistry $persistenceManager Реестр менеджеров Doctrine
     */
    public function __construct(ManagerRegistry $persistenceManager)
    {
        parent::__construct($persistenceManager, User::class);
    }

    /**
     * Обновляет хеш пароля пользователя.
     * Реализует метод интерфейса PasswordUpgraderInterface.
     * 
     * @param PwdAuthUserIface $account Пользователь, чей пароль нужно обновить
     * @param string $freshHashedPass Новый хеш пароля
     * @throws UnsupportedUserException Если передан неподдерживаемый тип пользователя
     */
    public function upgradePassword(PwdAuthUserIface $account, string $freshHashedPass): void
    {
        if (!($account instanceof User)) {
            throw new UnsupportedUserException(sprintf(
                'Instances of "%s" are not supported.',
                get_class($account)
            ));
        }

        $account->setPassword($freshHashedPass);
        $this->getEntityManager()->persist($account);
        $this->getEntityManager()->flush();
    }
}
