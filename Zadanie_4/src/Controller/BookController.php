<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookFormType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * BookController обрабатывает все операции, связанные с книгами в библиотечной системе.
 * Включает создание, изменение, удаление и скачивание книг.
 */
#[Route("/library")]
class BookController extends AbstractController
{
    /**
     * Обрабатывает загрузку файлов для обложек книг и их содержимого.
     * 
     * @param mixed $uploadedFile Загруженный файл
     * @param string $storageParameter Имя параметра для директории хранения
     * @param Book $libraryItem Сущность книги для связывания с файлом
     * @param SluggerInterface $fileNameGenerator Сервис для генерации безопасных имен файлов
     * @param EntityManagerInterface $databaseManager Менеджер базы данных для сохранения изменений
     * @param string $fileCategory Тип загружаемого файла ('cover' или 'content')
     * @return bool True если загрузка успешна, false в противном случае
     */
    private function processFileUpload(
        mixed $uploadedFile,
        string $storageParameter,
        Book $libraryItem,
        SluggerInterface $fileNameGenerator,
        EntityManagerInterface $databaseManager,
        string $fileCategory
    ): bool {
        if (!$uploadedFile) {
            return true;
        }

        $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedName = $fileNameGenerator->slug($originalName);
        $uniqueFilename = $sanitizedName . "-" . uniqid() . "." . $uploadedFile->guessExtension();

        $dateFolderPath = (new \DateTime())->format("Y/m");
        $destinationPath = $this->getParameter($storageParameter) . "/" . $dateFolderPath;

        $filesystemManager = new Filesystem();

        $existingFilePath = null;
        if ($fileCategory === "cover" && $libraryItem->getCoverImageLocation()) {
            $existingFilePath = $this->getParameter("covers_directory") . "/" . $libraryItem->getCoverImageLocation();
        } elseif ($fileCategory === "content" && $libraryItem->getDigitalFileLocation()) {
            $existingFilePath = $this->getParameter("books_directory") . "/" . $libraryItem->getDigitalFileLocation();
        }

        if ($existingFilePath && $filesystemManager->exists($existingFilePath)) {
            $filesystemManager->remove($existingFilePath);
        }

        try {
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $uploadedFile->move($destinationPath, $uniqueFilename);

            if ($fileCategory === "cover") {
                $libraryItem->setCoverImageLocation($dateFolderPath . "/" . $uniqueFilename);
                $libraryItem->setSourceCoverFilename($uploadedFile->getClientOriginalName());
            } elseif ($fileCategory === "content") {
                $libraryItem->setDigitalFileLocation($dateFolderPath . "/" . $uniqueFilename);
                $libraryItem->setSourceBookFilename($uploadedFile->getClientOriginalName());
            }

            return true;
        } catch (FileException $exception) {
            $this->addFlash("error", "File upload error (" . $fileCategory . "): " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Создает новую запись книги в библиотеке.
     * Обрабатывает как метаданные книги, так и загрузку файлов (обложка и содержимое).
     * 
     * @param Request $request HTTP запрос
     * @param EntityManagerInterface $databaseManager Менеджер базы данных для сохранения новой книги
     * @param SluggerInterface $fileNameGenerator Сервис для генерации безопасных имен файлов
     * @return Response Отображение формы или редирект
     */
    #[Route("/add", name: "app_book_new", methods: ["GET", "POST"])]
    #[IsGranted("IS_AUTHENTICATED_REMEMBERED")]
    public function createNewItem(
        Request $request,
        EntityManagerInterface $databaseManager,
        SluggerInterface $fileNameGenerator
    ): Response {
        $libraryItem = new Book();

        $itemForm = $this->createForm(BookFormType::class, $libraryItem);
        $itemForm->handleRequest($request);

        if ($itemForm->isSubmitted() && $itemForm->isValid()) {
            $libraryItem->setOwnerUser($this->getUser());

            $coverImageFile = $itemForm->get("coverFile")->getData();
            $this->processFileUpload(
                $coverImageFile,
                "covers_directory",
                $libraryItem,
                $fileNameGenerator,
                $databaseManager,
                "cover"
            );

            $contentFile = $itemForm->get("bookFile")->getData();
            $this->processFileUpload(
                $contentFile,
                "books_directory",
                $libraryItem,
                $fileNameGenerator,
                $databaseManager,
                "content"
            );

            $databaseManager->persist($libraryItem);
            $databaseManager->flush();

            $this->addFlash("success", "New book successfully added to your library!");
            return $this->redirectToRoute("app_home");
        }

        return $this->render("book/new.html.twig", [
            "book" => $libraryItem,
            "form" => $itemForm->createView(),
        ]);
    }

    /**
     * Изменяет существующую запись книги в библиотеке.
     * Позволяет обновлять метаданные книги и управлять связанными файлами.
     * Включает функциональность удаления существующих файлов и загрузки новых.
     * 
     * @param Request $request HTTP запрос
     * @param Book $libraryItem Сущность книги для изменения
     * @param EntityManagerInterface $databaseManager Менеджер базы данных для сохранения изменений
     * @param SluggerInterface $fileNameGenerator Сервис для генерации безопасных имен файлов
     * @param Filesystem $filesystemManager Сервис для управления файловой системой
     * @return Response Отображение формы или редирект
     * @throws AccessDeniedException Если у пользователя нет прав на редактирование книги
     */
    #[Route("/{itemId}/modify", name: "app_book_edit", methods: ["GET", "POST"])]
    #[IsGranted("IS_AUTHENTICATED_REMEMBERED")]
    public function modifyExistingItem(
        Request $request,
        Book $libraryItem,
        EntityManagerInterface $databaseManager,
        SluggerInterface $fileNameGenerator,
        Filesystem $filesystemManager
    ): Response {
        if ($libraryItem->getOwnerUser() !== $this->getUser() && !$this->isGranted("ROLE_ADMIN")) {
            throw $this->createAccessDeniedException("You don't have permission to edit this library item.");
        }

        $itemForm = $this->createForm(BookFormType::class, $libraryItem);
        $itemForm->handleRequest($request);

        if ($itemForm->isSubmitted() && $itemForm->isValid()) {
            if ($itemForm->has("deleteCoverFile") && $itemForm->get("deleteCoverFile")->getData()) {
                if ($libraryItem->getCoverImageLocation()) {
                    $coverPath = $this->getParameter("covers_directory") . "/" . $libraryItem->getCoverImageLocation();
                    if ($filesystemManager->exists($coverPath)) {
                        $filesystemManager->remove($coverPath);
                    }
                    $libraryItem->setCoverImageLocation(null);
                    $libraryItem->setSourceCoverFilename(null);
                }
            }

            $coverImageFile = $itemForm->get("coverFile")->getData();
            if ($coverImageFile) {
                $this->processFileUpload(
                    $coverImageFile,
                    "covers_directory",
                    $libraryItem,
                    $fileNameGenerator,
                    $databaseManager,
                    "cover"
                );
            }

            if ($itemForm->has("deleteBookFile") && $itemForm->get("deleteBookFile")->getData()) {
                if ($libraryItem->getDigitalFileLocation()) {
                    $filePath = $this->getParameter("books_directory") . "/" . $libraryItem->getDigitalFileLocation();
                    if ($filesystemManager->exists($filePath)) {
                        $filesystemManager->remove($filePath);
                    }
                    $libraryItem->setDigitalFileLocation(null);
                    $libraryItem->setSourceBookFilename(null);
                }
            }

            $contentFile = $itemForm->get("bookFile")->getData();
            if ($contentFile) {
                $this->processFileUpload(
                    $contentFile,
                    "books_directory",
                    $libraryItem,
                    $fileNameGenerator,
                    $databaseManager,
                    "content"
                );
            }

            $databaseManager->flush();

            $this->addFlash("success", "Book details successfully updated!");
            return $this->redirectToRoute("app_home");
        }

        return $this->render("book/edit.html.twig", [
            "book" => $libraryItem,
            "form" => $itemForm->createView(),
        ]);
    }

    /**
     * Удаляет книгу из библиотеки.
     * Удаляет как запись в базе данных, так и связанные файлы (обложка и содержимое).
     * 
     * @param Request $request HTTP запрос
     * @param Book $libraryItem Сущность книги для удаления
     * @param EntityManagerInterface $databaseManager Менеджер базы данных для удаления книги
     * @param Filesystem $filesystemManager Сервис для управления файловой системой
     * @return Response Редирект на главную страницу
     * @throws AccessDeniedException Если у пользователя нет прав на удаление книги или неверный CSRF токен
     */
    #[Route("/{itemId}/remove", name: "app_book_delete", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_REMEMBERED")]
    public function removeItem(
        Request $request,
        Book $libraryItem,
        EntityManagerInterface $databaseManager,
        Filesystem $filesystemManager
    ): Response {
        if ($libraryItem->getOwnerUser() !== $this->getUser() && !$this->isGranted("ROLE_ADMIN")) {
            throw $this->createAccessDeniedException("You don't have permission to delete this library item.");
        }

        if (!$this->isCsrfTokenValid("delete" . $libraryItem->getItemId(), $request->request->get("_token"))) {
            throw $this->createAccessDeniedException("Invalid CSRF token.");
        }

        if ($libraryItem->getCoverImageLocation()) {
            $coverPath = $this->getParameter("covers_directory") . "/" . $libraryItem->getCoverImageLocation();
            if ($filesystemManager->exists($coverPath)) {
                $filesystemManager->remove($coverPath);
            }
        }

        if ($libraryItem->getDigitalFileLocation()) {
            $filePath = $this->getParameter("books_directory") . "/" . $libraryItem->getDigitalFileLocation();
            if ($filesystemManager->exists($filePath)) {
                $filesystemManager->remove($filePath);
            }
        }

        $databaseManager->remove($libraryItem);
        $databaseManager->flush();

        $this->addFlash("success", "Book successfully removed from your library.");

        return $this->redirectToRoute("app_home");
    }

    /**
     * Обрабатывает скачивание цифрового содержимого книги.
     * 
     * @param Book $libraryItem Сущность книги, содержащая файл для скачивания
     * @return Response Ответ с файлом для скачивания
     * @throws NotFoundException Если файл не существует или пользователь не аутентифицирован
     */
    #[Route("/{itemId}/download", name: "app_book_download", methods: ["GET"])]
    public function downloadContent(Book $libraryItem): Response {
        if (!$libraryItem->getDigitalFileLocation() || !$this->getUser()) {
            throw $this->createNotFoundException("Digital file not found or access denied.");
        }

        $filePath = $this->getParameter("books_directory") . "/" . $libraryItem->getDigitalFileLocation();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException("Digital file not found on server.");
        }

        return $this->file($filePath, $libraryItem->getSourceBookFilename());
    }
}
