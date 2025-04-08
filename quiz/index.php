<?php
$servername = "localhost";
$username = "root"; // replace with your MySQL username
$password = ""; // replace with your MySQL password
$dbname = "quiz_app";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Name = $_POST['username'];
    $score = $_POST['score'];

    // Insert into leaderboard table
    $sql = "INSERT INTO leaderboard (name, score) VALUES ('$Name', '$score')";
    $conn->query($sql);
}

// Fetch existing leaderboard entries
$result = $conn->query("SELECT name, score FROM leaderboard ORDER BY score DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css?v=2">

    <title>Quiz App</title>
</head>
<body>
    <div id="quiz-container">
        <h1>Test Your Knowledge!</h1>
        <div id="question-container">
            <p id="question"></p>
            <div id="answer-buttons" class="btn-container"></div>
        </div>
        <button id="next-btn" class="btn" style="display: none;">Next</button>
    </div>

    <form id="name-form" method="POST" style="display: none;">
        <input type="text" name="username" placeholder="Enter your name" required>
        <input type="hidden" name="score" id="score-input">
        <button type="submit">Submit</button>
    </form>
    
    <div id="leaderboard">

        <h2>Leaderboard</h2>
        <ul id="leaderboard-list">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($row['name']) . ": " . htmlspecialchars($row['score']) . "</li>";
                }
            }
            ?>
        </ul>
    </div>

    <form id="name-form" method="POST" style="display: none;">
        <input type="text" name="username" placeholder="Enter your name" required>
        <input type="hidden" name="score" id="score-input">
        <button type="submit">Submit</button>
    </form>

    <script>
        const questionElement = document.getElementById('question');
        const answerButtons = document.getElementById('answer-buttons');
        const nextButton = document.getElementById('next-btn');
        const leaderboardList = document.getElementById('leaderboard-list');
        const scoreInput = document.getElementById('score-input');
        const nameForm = document.getElementById('name-form');

        let currentQuestionIndex = 0;
        let questions = [];
        let score = 0;

        async function fetchQuestions() {
            try {
                const response = await fetch('https://the-trivia-api.com/v2/questions');
                if (!response.ok) throw new Error('Network error');
                
                const data = await response.json();
                questions = data;
                showQuestion();
            } catch (error) {
                console.error('Error fetching questions:', error);
            }
        }

        function showQuestion() {
            resetState();
            if (currentQuestionIndex >= questions.length) {
                alert("Quiz finished!");
                return;
            }

            const questionData = questions[currentQuestionIndex];
            questionElement.innerText = "Question " + (currentQuestionIndex + 1) + ": " + questionData.question.text;


            let allAnswers = [...questionData.incorrectAnswers, questionData.correctAnswer];
            allAnswers.sort(() => Math.random() - 0.5); // Shuffle answers

            allAnswers.forEach(answer => {
                const button = document.createElement('button');
                button.innerText = answer;
                button.classList.add('btn');
                button.addEventListener('click', () => selectAnswer(answer, questionData.correctAnswer));
                answerButtons.appendChild(button);
            });

            nextButton.style.display = 'none';
        }

        function resetState() {
            nextButton.style.display = 'none';
            answerButtons.innerHTML = '';
        }

        function selectAnswer(selected, correct) {
            if (selected === correct) {
                score++;
            }

            currentQuestionIndex++;

            if (currentQuestionIndex % 10 === 0) {
                scoreInput.value = score; // Set score in hidden input
                nameForm.style.display = 'block'; // Show form
            } else {
                nextButton.style.display = 'block';
            }
        }

        nextButton.addEventListener('click', showQuestion);

        fetchQuestions();
    </script>
</body>
</html>
