<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM tasks WHERE assigned_to = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
</head>
<body>
<div id="app">

    <div v-if="showToast" class="toast">{{ toastMessage }}</div>

    <div class="container">
        <header>
            <h2>Welcome, <?php echo $_SESSION['name']; ?> (User)</h2>
        </header>

        <section class="task-list">
            <h3>Your Tasks</h3>

            <?php if ($result->num_rows > 0): ?>
                <div class="task-card-container">
                    <?php while ($task = $result->fetch_assoc()) : ?>
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
                            <p><strong>Status:</strong> <span class="<?php echo $badgeClass; ?>"><?php echo $status; ?></span></p>
                            <p><strong>Deadline:</strong> <?php echo $task['deadline']; ?></p>
                            <p><strong>Created:</strong> <?php echo $task['created_at']; ?></p>

                            <div class="task-actions">
                                <form method="POST" action="update_status.php">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <select name="status" required>
                                        <option value="">-- Update Status --</option>
                                        <option value="Pending" <?php if ($status === 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="In Progress" <?php if ($status === 'In Progress') echo 'selected'; ?>>In Progress</option>
                                        <option value="Completed" <?php if ($status === 'Completed') echo 'selected'; ?>>Completed</option>
                                    </select>
                                    <button type="submit" class="btn btn-edit">Update</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #777;">You have no assigned tasks.</p>
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
