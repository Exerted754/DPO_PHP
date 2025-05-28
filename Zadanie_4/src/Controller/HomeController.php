<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * HomeController обрабатывает отображение главной страницы и каталога библиотеки.
 * Отвечает за показ списка книг и управление их отображением.
 */
class HomeController extends AbstractController
{
    /**
     * Отображает каталог книг в библиотеке.
     * Показывает список всех книг, отсортированных по дате завершения в порядке убывания.
     * 
     * @param BookRepository $libraryRepository Репозиторий для работы с книгами
     * @return Response Отображение страницы каталога
     */
    #[Route("/", name: "app_home")]
    #[Route("/catalog", name: "app_catalog")]
    public function displayCatalog(BookRepository $libraryRepository): Response
    {
        $currentVisitor = $this->getUser();
        $libraryCollection = [];

        if ($currentVisitor instanceof UserInterface) {
            $libraryCollection = $libraryRepository->fetchAllSortedByCompletionDateDescending();
        } else {
            $libraryCollection = $libraryRepository->fetchAllSortedByCompletionDateDescending();
        }

        return $this->render("home/index.html.twig", [
            "books" => $libraryCollection,
            "pageTitle" => "Personal Library Catalog"
        ]);
    }
}
