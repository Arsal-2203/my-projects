<?php

// Connect to MySQL Database
$servername = "localhost";
$username = "root";
$password = "";
$database = "todo_app";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission (Add Task)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task'])) {
    $task = trim($_POST['task']);
    if (!empty($task)) {
        $stmt = $conn->prepare("INSERT INTO tasks (task) VALUES (?)");
        $stmt->bind_param("s", $task);
        if ($stmt->execute()) {

            // Optionally, you can add a success message here
        } else {
            echo "Error: " . $stmt->error; // Display error message if insertion fails
        }
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}

// Handle task completion
if (isset($_GET['complete'])) {
    $id = (int) $_GET['complete'];
    $conn->query("UPDATE tasks SET completed = 1 WHERE id = $id");
    header("Location: index.php");
    exit();
}

// Handle task deletion
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM tasks WHERE id = $id");
    header("Location: index.php");
    exit();
}

// Fetch tasks from the database
$result = $conn->query("SELECT * FROM tasks ORDER BY id DESC");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4" style="background-image: url('to do.png'); background-size: cover; background-position: center;">
    <h2 class="mb-3 text-center"> To-Do List</h2>
    <form method="POST" action="index.php" class="mb-3">
        <input type="text" name="task" class="form-control" placeholder="Enter a new task" required>
        <button type="submit" class="btn btn-primary mt-2">Add Task</button>
    </form>
    <ul class="list-group">
        <?php while ($row = $result->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span style="text-decoration: <?php echo ($row['completed'] == 1) ? 'line-through' : 'none'; ?>;">
                    <?php echo htmlspecialchars($row['task']); ?>
                </span>
                <div>
                    <a href="?complete=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Complete</a>
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
