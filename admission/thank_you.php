<?php
$course = isset($_GET['course']) ? htmlspecialchars($_GET['course']) : 'the course';
$name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <link rel="icon" href="favicon.png" type="image/png"> <!-- Updated favicon link -->
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 20px;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-image: url('admission.jpg');
            background-size: cover;
            background-position: center;
            color: black;
            text-align: center;
        }
        header, footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1em;
        }
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1em;
        }
    </style>
</head>
<body>
    <header>
        <h1>Thank You!</h1>
    </header>
    <main>
        <p>Thank you for taking admission in <?php echo $course; ?>. We appreciate your interest and look forward to seeing you soon!</p>
    </main>
    <footer>
        <p>&copy; 2023 My Amazing Page</p>
    </footer>
</body>
</html>
