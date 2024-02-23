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

function submitComment() {
    const commentContent = document.querySelector('textarea[name="commentContent"]').value;
    const imageId = document.querySelector('#currentImageId').value;

    fetch('submit_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            commentContent: commentContent,
            imageId: imageId
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message);
        } else {
            alert(data.message); // Display error message as an alert
        }
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
        showNotification('Error submitting comment. Please try again later.');
    });
}

// Function to show a popup notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);

    // Automatically hide the notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
