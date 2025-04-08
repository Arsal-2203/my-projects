<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $course = $_POST['course'];

    // Check if the email is already taken
    $checkEmailSql = "SELECT * FROM user WHERE email='$email'";
    $result = $conn->query($checkEmailSql);

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: The email address is already taken. Please use a different email.');</script>";
    } else {
        $sql = "INSERT INTO user (name, email, dob , course) VALUES ('$name', '$email', '$dob' , '$course')";

        if ($conn->query($sql) === TRUE) {
            header("Location: thank_you.php?course=" . urlencode($course) . "&name=" . urlencode($name));
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Amazing Page</title>
    <link rel="icon" href="favicon.png" type="image/png"> <!-- Updated favicon link -->
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif; /* Changed font family */
            font-size: 18px; /* Changed font size */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-image: url('admission.jpg');
            background-size: cover;
            background-position: center;
            color: #333;
        }
        header, footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1em;
        }
        main {
            flex: 1;
            padding: 1em;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form {
            width: 400px; /* Increased form width */
            margin: 2em auto;
            padding: 1em;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        form label {
            display: block;
            margin-bottom: 0.5em;
            font-weight: bold;
        }
        form input, form select {
            width: 100%;
            padding: 0.5em;
            margin-bottom: 1em;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        form button {
            width: 100%;
            padding: 0.75em;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to My Amazing Page</h1>
    </header>
    <main>
        <form id="admissionForm" method="POST" action="">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br><br>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>
            
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required><br><br>
            
            <label for="course">Course:</label>
            <select id="course" name="course" required>
                <option value="computerScience">Computer Science</option>
                <option value="business">Business</option>
                <option value="arts">Arts</option>
                <option value="Sciences">Sciences</option>
            </select><br><br>

            <div id="sciencesSubDropdown" style="display: none;">
                <label for="sciencesSub">Select a Science Subject:</label>
                <select id="sciencesSub" name="sciencesSub">
                    <option value="physics">Physics</option>
                    <option value="chemistry">Chemistry</option>
                    <option value="biology">Biology</option>
                </select><br><br>
            </div>
            
            <button type="submit">Submit</button>
        </form>
    </main>/main>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const courseSelect = document.getElementById("course");
        const sciencesSubDropdown = document.getElementById("sciencesSubDropdown");

        courseSelect.addEventListener("change", function () {
            if (courseSelect.value === "sciences") {
                sciencesSubDropdown.style.display = "block";
            } else {
                sciencesSubDropdown.style.display = "none";
            }
        });
    });
</script>

    <footer>
        <p>&copy; 2023 My Amazing Page</p>
    </footer>
    <script>
        function showAlert() {
            alert('Hello! You clicked the button!');
        }

        document.getElementById('course').addEventListener('change', function() {
            var sciencesSubDropdown = document.getElementById('sciencesSubDropdown');
            if (this.value === 'Sciences') {
                sciencesSubDropdown.style.display = 'block';
            } else {
                sciencesSubDropdown.style.display = 'none';
            }
        });

        document.getElementById('admissionForm').addEventListener('submit', function(event) {
            event.preventDefault();
            var name = document.getElementById('name').value;
            var email = document.getElementById('email').value;
            var dob = document.getElementById('dob').value;
            var course = document.getElementById('course').value;
            var sciencesSub = document.getElementById('sciencesSub').value;

            if (name && email && dob && course && (course !== 'Sciences' || sciencesSub)) {
                this.submit();
            } else {
                alert('Please fill out all fields.');
            }
        });
    </script>
</body>
</html>
