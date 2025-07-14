<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?deleted=1");
        exit;
    } else {
        echo "Error deleting task.";
    }
} else {
    echo "Invalid request.";
}
?>
