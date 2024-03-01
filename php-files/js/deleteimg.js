function confirmDelete(fileId) {
    if (confirm("Are you sure you want to delete this file and all associated comments?")) {
        // Make an AJAX request to delete the file and its comments
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "profile.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Remove the deleted file element from the DOM
                    var fileElement = document.getElementById('file_' + fileId);
                    if (fileElement) {
                        fileElement.parentNode.removeChild(fileElement);
                    }
                }
                alert(response.message);
            }
        };
        xhr.send("file_id=" + fileId);
    }
}