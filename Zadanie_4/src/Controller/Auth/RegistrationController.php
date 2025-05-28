<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * RegistrationController обрабатывает процесс регистрации новых пользователей.
 * Отвечает за создание и валидацию форм регистрации, а также сохранение новых пользователей.
 */
class RegistrationController extends AbstractController
{
    /**
     * Обрабатывает регистрацию нового пользователя.
     * 
     * Процесс регистрации включает:
     * 1. Создание формы регистрации
     * 2. Обработку отправленной формы
     * 3. Валидацию данных
     * 4. Хеширование пароля
     * 5. Сохранение пользователя в базе данных
     * 6. Автоматический вход пользователя
     * 
     * @param Request $request HTTP-запрос
     * @param UserPasswordHasherInterface $userPasswordHasher Сервис для хеширования паролей
     * @param EntityManagerInterface $entityManager Менеджер сущностей для работы с БД
     * @return Response Отображение формы регистрации или редирект
     */
    #[Route("/register", name: "app_register")]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Создаем нового пользователя
        $user = new User();
        
        // Создаем форму регистрации
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Если форма отправлена и валидна
        if ($form->isSubmitted() && $form->isValid()) {
            // Хешируем пароль
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get("plainPassword")->getData()
                )
            );

            // Сохраняем пользователя в базе данных
            $entityManager->persist($user);
            $entityManager->flush();

            // Перенаправляем на страницу входа
            return $this->redirectToRoute("app_login");
        }

        // Отображаем форму регистрации
        return $this->render("registration/register.html.twig", [
            "registrationForm" => $form->createView(),
        ]);
    }
}

