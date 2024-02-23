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
}

function changeSlide(n) {
    showSlide(slideIndex += n);
}

function setImageForComment(imageId) {
    document.querySelector('input[name="imageId"]').value = imageId;
    loadCommentsForImage(imageId); // Load comments for this image
}