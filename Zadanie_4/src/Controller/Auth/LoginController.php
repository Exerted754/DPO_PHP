<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * LoginController обрабатывает процессы входа и выхода пользователей.
 * Отвечает за отображение формы входа и обработку ошибок аутентификации.
 */
class LoginController extends AbstractController
{
    /**
     * Обрабатывает вход пользователя в систему.
     * 
     * Процесс входа включает:
     * 1. Проверку, не авторизован ли уже пользователь
     * 2. Получение последней ошибки аутентификации
     * 3. Получение последнего введенного имени пользователя
     * 4. Отображение формы входа
     * 
     * @param AuthenticationUtils $authenticationUtils Сервис для работы с аутентификацией
     * @return Response Отображение формы входа или редирект
     */
    #[Route(path: "/login", name: "app_login")]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Проверяем, не авторизован ли уже пользователь
        if ($this->getUser()) {
            return $this->redirectToRoute("app_home");
        }

        // Получаем последнюю ошибку аутентификации и имя пользователя
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Отображаем форму входа
        return $this->render("auth/login.html.twig", [
            "last_username" => $lastUsername,
            "error" => $error,
        ]);
    }

    /**
     * Обрабатывает выход пользователя из системы.
     * 
     * Примечание: Этот метод никогда не будет вызван напрямую.
     * Выход из системы обрабатывается брандмауэром безопасности Symfony.
     * 
     * @throws \LogicException Всегда выбрасывает исключение, так как метод не должен быть вызван напрямую
     */
    #[Route(path: "/logout", name: "app_logout")]
    public function logout(): void
    {
        throw new \LogicException("This method can be blank - it will be intercepted by the logout key on your firewall.");
    }
}

