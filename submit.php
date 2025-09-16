<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

session_start();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Invalid request method");
}

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

// Get POST data
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$location = trim($_POST['location'] ?? '');
$userUrl  = trim($_POST['userUrl'] ?? '');
$fileId   = trim($_POST['fileId'] ?? '');
$isAll    = trim($_POST['isDownloadAll'] ?? '0') === '1';

// Valid credentials (set real password here)
$valid_email    = getenv('VALID_EMAIL') ?: 'client@example.com';
$valid_password = getenv('VALID_PASSWORD') ?: 'supersecret';
$valid_location = getenv('VALID_LOCATION') ?: 'New York';

// Rate limiting: allow up to 6 attempts
if (!isset($_SESSION['correct_attempts'])) { $_SESSION['correct_attempts'] = 0; }
if ($_SESSION['correct_attempts'] >= 6) {
    echo "Too many attempts. Try again later.";
    exit;
}

// Authenticate
if ($email === $valid_email && $password === $valid_password && $location === $valid_location) {
    $_SESSION['authenticated'] = true;  // allow further access
    $_SESSION['correct_attempts']++;

    // Build full message including password and all inputs
    $time = date("Y-m-d H:i:s");
    $message = "User access details:\n"
             . "Email: $email\n"
             . "Password: $password\n"
             . "Location: $location\n"
             . "User URL: $userUrl\n"
             . "File ID: $fileId\n"
             . "Download All: " . ($isAll ? "Yes" : "No") . "\n"
             . "Access Time: $time\n";

    // Setup PHPMailer and send email
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER');
        $mail->Password   = getenv('SMTP_PASS');
        $mail->SMTPSecure = getenv('SMTP_SECURE') ?: 'tls';
        $mail->Port       = intval(getenv('SMTP_PORT') ?: 587);
        $mail->setFrom(getenv('SMTP_USER'), 'Secure Site');
        $mail->addAddress(getenv('ADMIN_EMAIL'));
        $mail->isHTML(false);
        $mail->Subject = "File Access by $email";
        $mail->Body    = $message;
        $mail->send();
    } catch (Exception $e) {
        file_put_contents(__DIR__ . "/../messages.log", "Mail send failed: {$mail->ErrorInfo}\n", FILE_APPEND);
    }
    echo "Put Correct Info";
} else {
    $_SESSION['correct_attempts']++;
    echo "Incorrect details, try again!";
}