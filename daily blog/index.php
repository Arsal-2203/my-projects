<?php
include 'connection.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errorMessage = ""; // Initialize error message variable

    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['blog-content'])) {
        // Debugging: Log the incoming data
        error_log(print_r($_POST, true));

        $name = $_POST['name'];
        $email = $_POST['email'];
        $blogContent = $_POST['blog-content'];

        // Prepare and bind
        if (empty($name) || empty($email) || empty($blogContent)) {
            $errorMessage = "Error: All fields are required."; // Set error message
        }

        if (!empty($errorMessage)) {
            echo $errorMessage; // Display error message
            return; // Exit the function if there are errors
        }
        
        $stmt = $conn->prepare("INSERT INTO blogs (name, email, content) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $blogContent);

        // Execute the statement
        if ($stmt->execute()) {
            echo "New blog entry created successfully"; // Success message
            header("Location: index.php"); // Redirect to the same page
            exit();
        } else {
            error_log("Error: " . $stmt->error); // Log the error
            echo "Error: Could not create blog entry.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Blog</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
</head>
<body>

    <header>
        <h1>Welcome to Daily Blog</h1>
    </header>
    <main>
        <section id="posts">
            <h2>Blog Posts</h2>
            <article>
                <h3>Sample Post Title</h3>
                <p>This is a sample blog post content. More posts will be added here.</p>
            </article>
        </section>
        <section id="blog-form">
            <h2>Submit Your Blog</h2>
            <form id="submit-blog-form" action="index.php" method="POST"> <!-- Added action and method -->

                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="blog-content">Blog Content:</label>
                <textarea id="blog-content" name="blog-content" rows="4" required></textarea>
                
                <button type="submit">Submit</button>
            </form>
        </section>

<section id="user-blogs">

            <h2>Your Blogs</h2>
            <div id="blogs-list">
                <?php
$result = $conn->query("SELECT name, email, content FROM blogs ORDER BY created_at DESC"); // Ensure created_at exists

                while ($row = $result->fetch_assoc()) {
                    echo "<article><h3>" . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['email']) . ")</h3><p>" . htmlspecialchars($row['content']) . "</p></article>";
                }
                ?>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 Daily Blog. All rights reserved.</p>
    </footer>
</body>
</html>
