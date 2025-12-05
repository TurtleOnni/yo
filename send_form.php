<?php
// Файл обработки формы

// Настройки
$to_email = "lkgromova@yandex.ru"; // Ваш email
$subject = "Новая заявка с сайта Путешествия в Китай";
$redirect_url = "index.html"; // или index.php, если переименовали

// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Получаем данные из формы
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $phone = trim($_POST["phone"]);
    
    // Валидация данных
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = "Имя не заполнено";
    }
    
    if (empty($last_name)) {
        $errors[] = "Фамилия не заполнена";
    }
    
    if (empty($phone)) {
        $errors[] = "Телефон не заполнен";
    } elseif (!preg_match('/^\+7\s?\(?\d{3}\)?\s?\d{3}[\s-]?\d{2}[\s-]?\d{2}$/', $phone)) {
        $errors[] = "Неверный формат телефона";
    }
    
    // Если есть ошибки, перенаправляем с сообщением об ошибке
    if (!empty($errors)) {
        $error_message = urlencode(implode(", ", $errors));
        header("Location: $redirect_url?error=$error_message#contact-form");
        exit;
    }
    
    // Подготовка данных для email
    $message = "Новая заявка с сайта Путешествия в Китай:\n\n";
    $message .= "Имя: " . htmlspecialchars($first_name) . "\n";
    $message .= "Фамилия: " . htmlspecialchars($last_name) . "\n";
    $message .= "Телефон: " . htmlspecialchars($phone) . "\n";
    $message .= "\nДата и время отправки: " . date("Y-m-d H:i:s") . "\n";
    $message .= "IP адрес: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    // Дополнительные заголовки
    $headers = "From: noreply@china-travel.ru\r\n";
    $headers .= "Reply-To: noreply@china-travel.ru\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Отправка email
    $mail_sent = mail($to_email, $subject, $message, $headers);
    
    // Логирование в файл (на случай если email не отправится)
    $log_data = date("Y-m-d H:i:s") . " | " . $_SERVER['REMOTE_ADDR'] . " | " 
                . $first_name . " " . $last_name . " | " . $phone . " | " 
                . ($mail_sent ? "Email sent" : "Email failed") . "\n";
    file_put_contents("form_submissions.log", $log_data, FILE_APPEND);
    
    // Перенаправление с сообщением об успехе или ошибке
    if ($mail_sent) {
        header("Location: $redirect_url?success=1#contact-form");
    } else {
        header("Location: $redirect_url?error=Ошибка при отправке письма. Попробуйте позже.#contact-form");
    }
    
    exit;
} else {
    // Если кто-то пытается напрямую открыть файл обработки
    header("Location: $redirect_url");
    exit;
}
?>