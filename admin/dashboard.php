<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$sql = "SELECT * FROM users WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$users = $stmt->get_result();

$tasks_sql = "SELECT tasks.*, users.name AS assignee FROM tasks 
              JOIN users ON tasks.assigned_to = users.id
              ORDER BY tasks.id DESC";
$tasks_result = $conn->query($tasks_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
</head>
<body>
<div id="app">

    <div v-if="showToast" class="toast">{{ toastMessage }}</div>

    <div class="container">
        <header>
            <h2>Welcome, <?php echo $_SESSION['name']; ?> (Admin)</h2>
            <a href="manage_users.php" class="btn-manage-users">
                <i class="fas fa-users-cog"></i> Manage Users
            </a>
        </header>

        <section class="assign-task">
            <h3>Assign New Task</h3>
            <form method="POST" action="assign_task.php">
                <label>Title:</label>
                <input type="text" name="title" required>

                <label>Description:</label>
                <textarea name="description" required></textarea>

                <label>Deadline:</label>
                <input type="date" name="deadline" required>

                <label>Assign to:</label>
                <select name="assigned_to" required>
                    <option value="">-- Select User --</option>
                    <?php while ($user = $users->fetch_assoc()) : ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo $user['name']; ?> (<?php echo $user['email']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Assign Task</button>
            </form>
        </section>

        <section class="task-list">
            <h3>All Tasks</h3>

            <?php if ($tasks_result->num_rows > 0): ?>
                <div class="task-card-container">
                    <?php while ($task = $tasks_result->fetch_assoc()) : ?>
                        <?php
                            $status = $task['status'];
                            $badgeClass = '';

                            switch (strtolower($status)) {
                                case 'pending':
                                    $badgeClass = 'badge badge-pending';
                                    break;
                                case 'in progress':
                                    $badgeClass = 'badge badge-progress';
                                    break;
                                case 'completed':
                                    $badgeClass = 'badge badge-completed';
                                    break;
                                default:
                                    $badgeClass = 'badge';
                            }
                        ?>
                        <div class="task-card">
                            <h4><?php echo $task['title']; ?></h4>
                            <p><strong>Description:</strong> <?php echo $task['description']; ?></p>
                            <p><strong>Assigned to:</strong> <?php echo $task['assignee']; ?></p>
                            <p><strong>Status:</strong> <span class="<?php echo $badgeClass; ?>"><?php echo $status; ?></span></p>
                            <p><strong>Deadline:</strong> <?php echo $task['deadline']; ?></p>
                            <p><strong>Created:</strong> <?php echo $task['created_at']; ?></p>

    
                            <div class="task-actions">
                                <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this task?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #777;">No tasks found.</p>
            <?php endif; ?>
        </section>
    </div>

    <a href="../logout.php" class="logout-float" title="Logout">
        <i class="fas fa-sign-out-alt"></i>
    </a>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            showToast: false,
            toastMessage: ''
        };
    },
    mounted() {
        const params = new URLSearchParams(window.location.search);
        const toast = params.get('toast');

        if (toast) {
            this.toastMessage = decodeURIComponent(toast);
            this.showToast = true;

            setTimeout(() => {
                this.showToast = false;
            }, 3000);
        }
    }
}).mount('#app');
</script>
</body>
</html>
