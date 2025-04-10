document.addEventListener('DOMContentLoaded', function() {
    // Skills Data
    const skills = [
        { name: 'HTML', icon: 'fab fa-html5' },
        { name: 'CSS', icon: 'fab fa-css3-alt' },
        { name: 'JavaScript', icon: 'fab fa-js' },
        { name: 'PHP', icon: 'fab fa-php' },
        { name: 'React', icon: 'fab fa-react' },
        { name: 'Node.js', icon: 'fab fa-node' }
    ];

    // Projects Data
    const projects = [
        {
            title: 'Admission Page',
            description: 'Student admission form with data validation and submission handling',
            link: 'http://localhost/github%20copilot/admission.php',
            code: 'https://github.com/Arsal-2203/my-projects',
            image: 'admission.png'
        },
        {
            title: 'Todo List',
            description: 'Task management app with add, complete and delete functionality',
            link: 'http://localhost/to%20do%20app/index.php',
            code: 'https://github.com/Arsal-2203/my-projects',
            image: 'to do.png'
        },
        {
            title: 'Quiz App', 
            description: 'Interactive quiz application with score tracking and multiple categories',
            link: 'http://localhost/quiz/#',
            code: 'https://github.com/Arsal-2203/my-projects',
            image: 'quiz.png'
        },
        {
            title: 'Weather App',
            description: 'Real-time weather information with location detection and forecasts',
            link: 'http://localhost/Weather/',
            code: 'https://github.com/Arsal-2203/my-projects',
            image: 'weather.png'
        },
        {
            title: 'Daily Blog',
            description: 'Content management system for daily blog posts with image uploads',
            link: 'http://localhost/daily%20blog/index.php',
            code: 'https://github.com/Arsal-2203/my-projects',
            image: 'blog.png'
        }
    ];

    // Populate Skills Section
    const skillsGrid = document.querySelector('.skills-grid');
    if (skillsGrid) {
        skills.forEach(skill => {
            const skillCard = document.createElement('div');
            skillCard.className = 'skill-card';
            skillCard.innerHTML = `
                <i class="${skill.icon}"></i>
                <h4>${skill.name}</h4>
            `;
            skillsGrid.appendChild(skillCard);
        });
    }

    // Populate Projects Section
    const projectsGrid = document.querySelector('.projects-grid');
    if (projectsGrid) {
        projects.forEach(project => {
            const projectCard = document.createElement('div');
            projectCard.className = 'project-card';
            projectCard.innerHTML = `
                <div class="project-image">
                    <img src="${project.image || 'assets/project-placeholder.jpg'}" alt="${project.title}">
                </div>
                <div class="project-info">
                    <h3>${project.title}</h3>
                    <p>${project.description}</p>
                    <div class="project-links">
                        <a href="${project.link}" target="_blank">Live Demo</a>
                        <a href="${project.code}" target="_blank">View Code</a>
                    </div>
                </div>
            `;
            projectsGrid.appendChild(projectCard);
        });
    }

    // Form validation
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate name
            const nameInput = document.getElementById('name');
            if (nameInput.value.trim() === '') {
                isValid = false;
                nameInput.style.borderColor = '#dc3545';
            } else {
                nameInput.style.borderColor = '#ddd';
            }

            // Validate email
            const emailInput = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                isValid = false;
                emailInput.style.borderColor = '#dc3545';
            } else {
                emailInput.style.borderColor = '#ddd';
            }

            // Validate message
            const messageInput = document.getElementById('message');
            if (messageInput.value.trim() === '') {
                isValid = false;
                messageInput.style.borderColor = '#dc3545';
            } else {
                messageInput.style.borderColor = '#ddd';
            }

            if (!isValid) {
                e.preventDefault();
                const errorMessage = document.createElement('div');
                errorMessage.className = 'form-message error';
                errorMessage.textContent = 'Please fill all required fields correctly';
                
                const existingMessage = contactForm.querySelector('.form-message');
                if (existingMessage) {
                    contactForm.replaceChild(errorMessage, existingMessage);
                } else {
                    contactForm.insertBefore(errorMessage, contactForm.firstChild);
                }
            } else {
                // Submit form data via AJAX
                const formData = new FormData(contactForm);
                
                fetch('process_contact.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const message = document.createElement('div');
                    message.className = data.success ? 'form-message success' : 'form-message error';
                    message.textContent = data.message;
                    
                    const existingMessage = contactForm.querySelector('.form-message');
                    if (existingMessage) {
                        contactForm.replaceChild(message, existingMessage);
                    } else {
                        contactForm.insertBefore(message, contactForm.firstChild);
                    }
                    
                    if (data.success) {
                        contactForm.reset();
                        setTimeout(() => message.remove(), 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
                
                e.preventDefault();
            }
        });

        // Clear validation on input
        const inputs = contactForm.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#ddd';
                const message = contactForm.querySelector('.form-message');
                if (message) {
                    message.remove();
                }
            });
        });
    }

    // Rest of existing code (theme toggle, animations etc.)
});
