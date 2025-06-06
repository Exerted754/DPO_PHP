# Личная библиотека книг (Обновленная версия)

Это обновленная версия проекта "Личная библиотека книг" с полностью переработанным дизайном и структурой кода. Проект представляет собой веб-приложение на базе Symfony для управления личной коллекцией книг с функциями авторизации и CRUD-операциями.

## Основные функции

- Регистрация и авторизация пользователей
- Просмотр списка книг (разные режимы для авторизованных и неавторизованных пользователей)
- Добавление новых книг с загрузкой обложек и файлов
- Редактирование существующих книг
- Удаление книг
- Скачивание файлов книг (если разрешено)

## Технические особенности

- Фреймворк: Symfony 6.4
- База данных: PostgreSQL
- Хранение файлов: организовано по дате загрузки для избежания конфликтов имен
- Авторизация: реализована с использованием сессий
- Интерфейс: адаптивный дизайн с использованием современных CSS-стилей

## Установка и запуск

### Требования

- PHP 8.1 или выше
- PostgreSQL 12 или выше
- Composer
- Symfony CLI (опционально, для локальной разработки)

### Шаги установки

1. Клонируйте репозиторий или распакуйте архив:
   ```
   git clone <repository-url> personal_library
   cd personal_library
   ```

2. Установите зависимости:
   ```
   composer install
   ```

3. Настройте подключение к базе данных в файле `.env`:
   ```
   DATABASE_URL="postgresql://username:password@127.0.0.1:5432/personal_library?serverVersion=15&charset=utf8"
   ```

4. Создайте базу данных и выполните миграции:
   ```
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. Создайте директории для загрузки файлов и установите права:
   ```
   mkdir -p public/uploads/covers public/uploads/books
   chmod -R 777 public/uploads
   ```

6. Запустите веб-сервер:
   ```
   symfony server:start
   ```
   или используйте встроенный PHP-сервер:
   ```
   php -S localhost:8000 -t public
   ```

7. Откройте приложение в браузере: `http://localhost:8000`

## Структура проекта

- `src/Entity/` - Сущности (User, Book)
- `src/Repository/` - Репозитории для работы с данными
- `src/Controller/` - Контроллеры для обработки запросов
- `src/Form/` - Типы форм
- `src/Security/` - Компоненты безопасности
- `templates/` - Twig-шаблоны
- `public/css/` - CSS-стили
- `public/uploads/` - Директория для загруженных файлов

## Особенности обновленной версии

- Полностью переработанный визуальный дизайн с новой цветовой схемой
- Улучшенная структура кода с более понятными именами переменных и методов
- Расширенная документация в коде
- Оптимизированная организация файлов и директорий
- Улучшенный пользовательский интерфейс с акцентом на удобство использования

## Лицензия

Этот проект распространяется под лицензией MIT.
