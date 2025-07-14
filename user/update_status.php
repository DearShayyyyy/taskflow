<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['status'];
    $user_id = $_SESSION['user_id'];

    $allowed_statuses = ['Pending', 'In Progress', 'Completed'];
    if (!in_array($new_status, $allowed_statuses)) {
        die("Invalid status update.");
    }

    $sql = "UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_status, $task_id, $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Failed to update task status.";
    }
} else {
   header("Location: dashboard.php?toast=" . urlencode("Task status updated successfully!"));
exit;
}
?>
