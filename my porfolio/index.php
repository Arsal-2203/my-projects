<?php
// Database connection
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "my portpolio";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Form submission handling
$message = '';
$messageClass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message_content = htmlspecialchars($_POST['message']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($message_content)) {
        $message = "Please fill all required fields";
        $messageClass = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
        $messageClass = "error";
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message_content);
        
        if ($stmt->execute()) {
            $message = "Thank you for your message! I'll get back to you soon.";
            $messageClass = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $messageClass = "error";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Portfolio</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo"> My Portfolio</div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#skills">Skills</a></li>
                <li><a href="#projects">Projects</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <div class="theme-toggle">
                <i class="fas fa-moon"></i>
            </div>
            <div class="hamburger">
                <div class="line"></div>
                <div class="line"></div>
                <div class="line"></div>
            </div>
        </nav>
    </header>

    <main>
        <section id="home" class="hero">
            <div class="hero-content">
                <h1>Hi, I'm <span class="name">MUHAMMAD ARSALAN</span></h1>
                <p class="title">Web Developer & Designer</p>
                <div class="cta-buttons">
                    <a href="#projects" class="btn">View Work</a>
                    <a href="#contact" class="btn btn-outline">Get In Touch</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="profile4.png" alt="Profile Picture" class="profile-pic">
            </div>
        </section>

        <section id="about" class="about">
            <h2>About Me</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>"I'm a passionate and motivated <i>web developer</i> currently in my <i>3rd semester</i> of studies.<br> I've recently completed a <i>full-stack development</i> course, gaining hands-on experience with frontend and backend technologies.<br> I specialize in <i>PHP, MySQL</i>, and modern web development tools, and I’m actively working on improving my skills by building protects. <br> I'm always eager to learn, take on challenges, and create clean, responsive, and user-friendly websites..</p>
                </div>
                <div class="skills-chart">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </section>

        <section id="skills" class="skills">
            <h2>My Skills</h2>
            <div class="skills-grid">
                <!-- Will be populated by JavaScript -->
            </div>
        </section>

        <section id="projects" class="projects">
            <h2>My Projects</h2>
            <div class="projects-grid">
                <!-- Will be populated by JavaScript -->
            </div>
        </section>

        <section id="contact" class="contact">
            <h2>Get In Touch</h2>
            <?php if (!empty($message)): ?>
                <div class="form-message <?php echo $messageClass; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form class="contact-form" method="POST" action="#contact">
                <div class="form-group">
                    <input type="text" id="name" name="name" placeholder="Your Name" required
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Your Email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <textarea id="message" name="message" placeholder="Your Message" required><?php 
                        echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; 
                    ?></textarea>
                </div>
                <button type="submit" class="btn">Send Message</button>
            </form>
        </section>
    </main>

    <footer>
        <div class="social-links">
            <a href="https://github.com/"><i class="fab fa-github"></i></a>
            <a href="https://www.linkedin.com/in/muhammad-arsalan-8781b2324/"><i class="fab fa-linkedin"></i></a>
            <a href="0325-0077926"><i class="fab fa-whatsapp"></i></a>
        </div>
        <p>&copy; <?php echo date("Y"); ?> My Portfolio. All rights reserved.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
