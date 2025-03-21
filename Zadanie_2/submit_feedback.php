<?php

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Europe/Moscow');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$MAIL = $_ENV['MAIL'];
$KEY = $_ENV['KEY'];
$HOST = $_ENV['HOST'];
$USERNAME = $_ENV['USERNAME'];
$DB_NAME = $_ENV['DB_NAME'];
$PASSWORD = $_ENV['PASSWORD'];

try {
    // Подключение к базе данных
    $pdo = new PDO("pgsql:host=$HOST;dbname=$DB_NAME", $USERNAME, $PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");

    // Получение данных из $_POST
    $fio = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    // Валидация
    $errors = [];
    if (empty($fio)) {
        $errors[] = 'Введите полное ФИО!';
    } else {
        $nameParts = preg_split('/\s+/', $fio);
        if (count($nameParts) < 2 || count($nameParts) > 3) {
            $errors[] = 'ФИО должно содержать 2 или 3 слова!';
        }
        $nameRegex = '/^[a-zA-Zа-яА-ЯёЁ-]+$/u';
        foreach ($nameParts as $part) {
            if (!preg_match($nameRegex, $part)) {
                $errors[] = 'Недопустимый символ!';
            }
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный адрес электронной почты';
    }

    if (!preg_match('/^(\+7|8)[\d]{10}$/', $phone)) {
        $errors[] = 'Неверный формат телефона. Используйте +7 или 8 и 10 цифр.';
    }

    if (empty($comment)) {
        $errors[] = 'Комментарий обязателен!';
    }

    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode('<br>', $errors)
        ]);
        exit();
    }

    // Проверка на дубликаты
    $stmt = $pdo->prepare("SELECT created_at FROM applications WHERE email = :email AND created_at > NOW() - INTERVAL '1 HOUR'");
    $stmt->execute(['email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $retryTime = strtotime($row['created_at']) - time() - 7200;
        $retryAfter = date('H:i:s', $retryTime);
        echo json_encode([
            'success' => false,
            'message' => "Повторная отправка запрещена. Вы можете попробовать снова через $retryAfter."
        ]);
        exit();
    }

    // Сохранение в таблицу applications
    $stmt = $pdo->prepare("INSERT INTO applications (fio, email, phone, comment, created_at) VALUES (:fio, :email, :phone, :comment, NOW())");
    $stmt->execute([
        'fio' => $fio,
        'email' => $email,
        'phone' => $phone,
        'comment' => $comment
    ]);

    // Отправка письма через PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Настройки SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.mail.ru';
        $mail->SMTPAuth = true;
        $mail->Username = $MAIL;
        $mail->Password = $KEY;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Отправитель и получатель
        $mail->setFrom('alexander.bor1isov@mail.ru', 'Alexander Borisov');
        $mail->addAddress($email);

        // Содержание письма
        $mail->isHTML(true);
        $mail->Subject = 'Новая заявка с формы обратной связи';
        $mail->Body    = "
            <h1>Новая заявка</h1>
            <p><strong>Имя:</strong> $fio</p>
            <p><strong>E-mail:</strong> $email</p>
            <p><strong>Телефон:</strong> $phone</p>
            <p><strong>Комментарий:</strong> $comment</p>
            <p>С Вами свяжутся после " . date('H:i:s d.m.Y', strtotime('+1 hour 30 minutes')) . "</p>
        ";

        // Отправка письма
        if ($mail->send()) {
            echo json_encode([
                'success' => true,
                'message' => 'Письмо успешно отправлено.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Ошибка при отправке письма.'
            ]);
        }
    } catch (Exception $e) {
        error_log('Ошибка при отправке письма: ' . $mail->ErrorInfo);
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при отправке письма: ' . $mail->ErrorInfo
        ]);
    }

} catch (PDOException $e) {
    error_log('Ошибка базы данных: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка базы данных: ' . $e->getMessage()
    ]);
}
?>