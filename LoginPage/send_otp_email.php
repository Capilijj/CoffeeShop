<?php
// I-enable ang PHP error reporting habang nagde-develop (i-disable sa production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// I-load ang environment variables gamit ang Dotenv
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// I-load ang .env variables mula sa parent directory (hal., htdocs/)
// Siguraduhin na ang iyong .env file ay isang directory sa itaas kung saan matatagpuan ang script na ito.
// Halimbawa: kung ang send_otp_email.php ay nasa 'htdocs/your_project/scripts/', ang .env ay dapat nasa 'htdocs/your_project/'
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Exception $e) {
    // Hawakan ang mga error sa paglo-load ng Dotenv
    error_log("Dotenv Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server configuration error. Please contact support.']);
    exit();
}


$message = "";
$otp_status = "";

// Suriin kung ang email para sa verification ay nakatakda sa session. Kung hindi, i-redirect sa Login.
if (!isset($_SESSION['otp_email_for_verification'])) {
    // Para sa mga AJAX request, magpadala ng JSON response
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['status' => 'error', 'message' => 'No email found for OTP verification. Please restart the process.']);
        exit();
    } else {
        // Para sa direktang access
        header("Location: Login.php");
        exit();
    }
}

$recipient_email = $_SESSION['otp_email_for_verification'];
$otp_lifetime = 300; // 5 minuto

// Hawakan ang mga kasalukuyang mensahe mula sa mga nakaraang redirect (kung mayroon)
if (isset($_SESSION['otp_verification_message'])) {
    $message = $_SESSION['otp_verification_message'];
    $otp_status = $_SESSION['otp_verification_status'] ?? '';
    // I-clear ang mga mensahe ng session pagkatapos ipakita
    unset($_SESSION['otp_verification_message'], $_SESSION['otp_verification_status']);
}

// Bumuo ng OTP kung hindi naroroon o nag-expire na
$generate_new_otp = true;
if (isset($_SESSION['otp']) && isset($_SESSION['otp_created_at'])) {
    $time_elapsed = time() - $_SESSION['otp_created_at'];
    if ($time_elapsed < $otp_lifetime) {
        $generate_new_otp = false; // Valid pa rin ang OTP, huwag bumuo ng bago maliban kung tahasang hiniling (hal., resend button)
    }
}

// Suriin kung ito ay isang resend request (mula sa Login.js)
$is_resend_request = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend_otp');

if ($generate_new_otp || $is_resend_request) {
    // Bumuo ng bagong 6-digit na OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    $mail = new PHPMailer(true);
    try {
        // Mga setting ng server
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com'; // Gamitin ang .env o default
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_APP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls'; // Gamitin ang .env o default
        $mail->Port = $_ENV['MAIL_PORT'] ?? 587; // Gamitin ang .env o default

        // Debugging PHPMailer - i-uncomment ang mga ito para sa detalyadong log
        // $mail->SMTPDebug = 2; // I-enable ang verbose debug output
        // $mail->Debugoutput = function($str, $level) {
        //     error_log("PHPMailer Debug ($level): " . $str);
        // };

        // Mga tatanggap
        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? $_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME'] ?? 'CoffeeCraft OTP');
        $mail->addAddress($recipient_email); // Magdagdag ng tatanggap

        // Nilalaman
        $mail->isHTML(true); // Itakda ang format ng email sa HTML
        $mail->Subject = 'Your CoffeeCraft OTP Code';
        $mail->Body    = "<h3>Your OTP Code for CoffeeCraft is: <strong>" . htmlspecialchars($otp) . "</strong></h3><p>This code is valid for 5 minutes.</p>";
        $mail->AltBody = "Your OTP Code for CoffeeCraft is: " . htmlspecialchars($otp) . ". This code is valid for 5 minutes.";

        $mail->send();

        // I-store ang OTP sa session
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_created_at'] = time();

        $message = "A 6-digit OTP has been sent to " . htmlspecialchars($recipient_email) . ".";
        $otp_status = "success";

    } catch (Exception $e) {
        // I-log ang error para sa debugging sa server
        error_log("Mailer Error: " . $mail->ErrorInfo);
        // Magbigay ng mas user-friendly na mensahe
        $message = "Hindi maipadala ang email. Pakisuri ang iyong koneksyon sa internet o subukang muli sa ibang pagkakataon. Kung magpapatuloy ang problema, makipag-ugnayan sa suporta.";
        // Opsyonal, isama ang partikular na Mailer Error para sa development
        // $message .= " Mailer Error: {$mail->ErrorInfo}"; // I-uncomment para sa debugging
        $otp_status = "error";
        // I-clear ang OTP kung nabigo ang pagpapadala
        unset($_SESSION['otp'], $_SESSION['otp_created_at']);
    }
} else {
    // Kung ang OTP ay valid pa rin at hindi isang resend request, huwag bumuo ng bago o magpadala ng email
    if (empty($message)) { // Itakda lamang ang mensahe kung hindi pa nakatakda ng session
        $time_remaining = $_SESSION['otp_created_at'] + $otp_lifetime - time();
        $minutes = floor($time_remaining / 60);
        $seconds = $time_remaining % 60;
        $message = "An OTP was already sent to " . htmlspecialchars($recipient_email) . ". It is valid for " . $minutes . " min " . $seconds . " sec.";
        $otp_status = "info";
    }
}

// Para sa mga AJAX request, ibalik ang JSON
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo json_encode([
        'status' => $otp_status,
        'message' => $message,
        'time_remaining' => ($_SESSION['otp_created_at'] ?? 0) + $otp_lifetime - time() // Ipadala ang natitirang oras para sa timer
    ]);
    exit();
} else {
    // Para sa direktang access (hal., paunang paglo-load ng pahina)
    $_SESSION['otp_verification_message'] = $message;
    $_SESSION['otp_verification_status'] = $otp_status;
    header("Location: Login.php");
    exit();
}
?>
