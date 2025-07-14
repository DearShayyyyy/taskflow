<?php
session_start();
include '../includes/db.php';

require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Task ID missing.";
    exit;
}

$task_id = $_GET['id'];

$task_sql = "SELECT * FROM tasks WHERE id = ?";
$stmt = $conn->prepare($task_sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task_result = $stmt->get_result();
$task = $task_result->fetch_assoc();

$users_result = $conn->query("SELECT * FROM users WHERE role = 'user'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];
    $assigned_to = $_POST['assigned_to'];

    $update_sql = "UPDATE tasks SET title=?, description=?, deadline=?, status=?, assigned_to=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssii", $title, $description, $deadline, $status, $assigned_to, $task_id);

    if ($stmt->execute()) {
        
        $user_sql = "SELECT email, name FROM users WHERE id = ?";
        $stmt = $conn->prepare($user_sql);
        $stmt->bind_param("i", $assigned_to);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user = $user_result->fetch_assoc();

        $mail = new PHPMailer(true);

        try {
        
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = '9fa58d17ad6a7d';  
            $mail->Password = '34b3c3fdb6c06e';   
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('shay@taskflow.com', 'Shay Yun | TaskFlow Admin');
            $mail->addAddress($user['email'], $user['name']);

            $mail->Subject = "Task Updated: " . $title;
            $mail->Body = "Hi {$user['name']},\n\nA task assigned to you has been updated:\n\n"
                        . "• Title: $title\n"
                        . "• Description: $description\n"
                        . "• Deadline: $deadline\n"
                        . "• Status: $status\n\n"
                        . "Please log in to your dashboard to view more details.\n\n- TaskFlow";

            $mail->send();
        } catch (Exception $e) {
            echo "Email error: " . $mail->ErrorInfo;
        }

        header("Location: dashboard.php");
        exit;
    } else {
        echo "Failed to update task.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Task</h2>
        <form method="POST">
            <label>Title:</label>
            <input type="text" name="title" value="<?php echo $task['title']; ?>" required>

            <label>Description:</label>
            <textarea name="description" required><?php echo $task['description']; ?></textarea>

            <label>Deadline:</label>
            <input type="date" name="deadline" value="<?php echo $task['deadline']; ?>" required>

           <p><strong>Status:</strong> <?php echo $task['status']; ?></p>
            </select>

            <label>Assign to:</label>
            <select name="assigned_to" required>
                <?php while ($user = $users_result->fetch_assoc()) : ?>
                    <option value="<?php echo $user['id']; ?>" 
                        <?php if ($user['id'] == $task['assigned_to']) echo 'selected'; ?>>
                        <?php echo $user['name']; ?> (<?php echo $user['email']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Update Task</button>
        </form>
    </div>
</body>
</html>
