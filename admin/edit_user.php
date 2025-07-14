<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$id = $_GET['id'];
$message = "";

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: manage_users.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $name, $email, $hashed_password, $id);
    } else {
        $update_sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $name, $email, $id);
    }

    if ($update_stmt->execute()) {
        header("Location: manage_users.php");
        exit;
    } else {
        $message = "Error updating user.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit User</h2>
        <?php if (!empty($message)) echo "<p style='color: red;'>$message</p>"; ?>
        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>

            <label>New Password (leave blank to keep current):</label>
            <input type="password" name="password">

            <button type="submit">Update User</button>
        </form>
    </div>
</body>
</html>
