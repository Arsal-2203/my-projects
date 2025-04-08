<?php
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging: Log the incoming data
    error_log(print_r($_POST, true));

    $name = $_POST['name'];
    $email = $_POST['email'];
    $blogContent = $_POST['blog-content'];

    // Prepare and bind
    if (empty($name) || empty($email) || empty($blogContent)) {
        die("Error: All fields are required.");
    }

    $stmt = $conn->prepare("INSERT INTO blogs (name, email, content) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $blogContent);

    // Execute the statement
    if ($stmt->execute()) {
        echo "New blog entry created successfully";
        $stmt->close(); // Close the statement after execution
        $conn->close(); // Close the connection after execution
        return; // Prevent further execution
    } else {
        error_log("Error: " . $stmt->error); // Log the error
        echo "Error: Could not create blog entry.";
    }


    if ($stmt->execute()) {
        echo "New blog entry created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
