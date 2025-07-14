<?php
session_start();
include '../includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'];
    $assigned_to = $_POST['assigned_to'];

    $sql = "INSERT INTO tasks (title, description, deadline, assigned_to, status, created_at)
            VALUES (?, ?, ?, ?, 'Pending', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $description, $deadline, $assigned_to);
    $stmt->execute();

    $user_sql = "SELECT name, email FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $assigned_to);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '4ccf9c8627f029';
        $mail->Password = 'bb08d9a52090b2'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525;

        $mail->setFrom('shay@taskflow.com', 'Shay Yun - TaskFlow');
        $mail->addAddress($user['email'], $user['name']);

        $mail->Subject = "New Task Assigned: $title";
        $mail->Body    = "Hi {$user['name']},\n\nYou have a new task assigned to you in TaskFlow.\n\n" .
                         "Title: $title\n" .
                         "Description: $description\n" .
                         "Deadline: $deadline\n\n" .
                         "Please log in to update your task status.\n\n" .
                         "— TaskFlow System (Sent by Shay Yun)";

        $mail->send();
    } catch (Exception $e) {
        
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
    }

   header("Location: dashboard.php?toast=" . urlencode("Task assigned successfully!"));
exit;

}
?>
