<?php 
$db_host = "localhost";
$db_username = "root";
$db_passwd = "";

$conn = mysqli_connect($db_host, $db_username, $db_passwd) or die("Could not connect!\n");

$db_name = "db_analogrecords";
mysqli_select_db($conn, $db_name) or die("Could not select the database $dbname!\n". mysqli_error($conn));

$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

$MAIL_HOST = isset($MAIL_HOST) ? $MAIL_HOST : 'sandbox.smtp.mailtrap.io';
$MAIL_PORT = isset($MAIL_PORT) ? $MAIL_PORT : 2525;
$MAIL_USER = isset($MAIL_USER) ? $MAIL_USER : '444f47db248b5c';
$MAIL_PASS = isset($MAIL_PASS) ? $MAIL_PASS : 'e0fa6ef9ec1210';
$MAIL_FROM = isset($MAIL_FROM) ? $MAIL_FROM : 'no-reply@example.com';
$MAIL_FROM_NAME = isset($MAIL_FROM_NAME) ? $MAIL_FROM_NAME : 'Analog Records';

if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    try {
        $phpmailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $phpmailer->isSMTP();
        $phpmailer->Host = $MAIL_HOST;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $MAIL_USER;
        $phpmailer->Password = $MAIL_PASS;
        $phpmailer->Port = $MAIL_PORT;
        $phpmailer->setFrom($MAIL_FROM, $MAIL_FROM_NAME);
    } catch (Exception $e) {
        if (isset($phpmailer)) unset($phpmailer);
    }
}

?>
