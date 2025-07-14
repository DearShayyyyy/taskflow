<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$sql = "SELECT * FROM users WHERE role = 'user'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Users</h2>
        <a href="add_user.php" class="btn-add">➕ Add New User</a>

        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php while ($user = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']); ?></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['id']; ?>" class="btn-edit">Edit</a>
                        <a href="delete_user.php?id=<?= $user['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
