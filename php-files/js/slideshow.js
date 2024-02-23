let slideIndex = 0; // Global variable to keep track of the current slide

document.addEventListener('DOMContentLoaded', function() {
    fetch('slideshow.php')
        .then(response => response.json())
        .then(data => {
            initializeSlideshow(data);
            showSlide(slideIndex); // Show the first slide
        })
        .catch((error) => {
            console.error('Error fetching image data:', error);
        });
});

function initializeSlideshow(images) {
    const container = document.querySelector('.slideshow-container');
    images.forEach((image, index) => {
        const slide = document.createElement('div');
        slide.className = 'mySlides fade';
        // Include the image ID as a data attribute
        slide.setAttribute('data-imageId', image.id); // Assuming each image object has an id
        slide.innerHTML = `<img src="${image.filepath}" alt="${image.filename}" style="width:100%">`;
        container.appendChild(slide);
    });
}

function showSlide(n) {
    let slides = document.getElementsByClassName("mySlides");
    if (n >= slides.length) { slideIndex = 0; }
    if (n < 0) { slideIndex = slides.length - 1; }
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";  
    }
    slides[slideIndex].style.display = "block";  
    // Retrieve imageId from the current slide
    const currentSlide = slides[slideIndex];
    const imageId = currentSlide.getAttribute('data-imageId');
    // Update the hidden input for comments form
    document.getElementById('currentImageId').value = imageId;
    // Dynamically load comments for the current slide
    loadCommentsForImage(imageId);
}

function changeSlide(n) {
    showSlide(slideIndex += n);
}

function setImageForComment(imageId) {
    document.querySelector('input[name="imageId"]').value = imageId;
    loadCommentsForImage(imageId); // Load comments for this image
}

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