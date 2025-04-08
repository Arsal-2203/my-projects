document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('submit-blog-form').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Get form values
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const blogContent = document.getElementById('blog-content').value;

        // Send data to the server
        fetch('submit_blog.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'name': name,
                'email': email,
                'blog-content': blogContent
            })
        })
        .then(response => response.text())
        .then(data => {
            // Create a new blog entry
            const blogEntry = document.createElement('article');
            blogEntry.innerHTML = `<h3>${name} (${email})</h3><p>${blogContent}</p>`;

            // Append the new blog entry to the blogs list
            document.getElementById('blogs-list').appendChild(blogEntry);

            // Clear the form
            document.getElementById('submit-blog-form').reset();
        })
        .catch(error => console.error('Error:', error));
    });


});
