function loadCommentsForImage(imageId) {
    fetch(`load_comments.php?imageId=${imageId}`)
        .then(response => response.json())
        .then(comments => {
            const commentsContainer = document.querySelector('.comments-container');
            commentsContainer.innerHTML = ''; // Clear existing comments
            comments.forEach(comment => {
                const commentElement = document.createElement('div');
                commentElement.className = 'comment';
                commentElement.innerHTML = `
                    <p><strong>${escapeHTML(comment.name)}</strong> at ${escapeHTML(comment.created_at)}</p>
                    <p>${escapeHTML(comment.content)}</p>
                `;
                commentsContainer.appendChild(commentElement);
            });
        })
        .catch(error => console.error('Error loading comments:', error));
}

// Utility function to prevent XSS
function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, 
        tag => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', 
            "'": '&#39;', '"': '&quot;'
        }[tag] || tag));
}