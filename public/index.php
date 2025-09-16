const BACKEND_URL = 'https://<your-render-service>.onrender.com/submit.php';

const response = await fetch(BACKEND_URL, {
    method: 'POST',
    body: formData
});

const textResult = await response.text();

if (textResult.includes("Put Correct Info")) {
    loginStatus.style.color = 'green';
    loginStatus.textContent = 'Authentication successful! Preparing download...';
    hideLoginModal();

    if (currentDownloadAll) {
        window.location.href = 'https://<your-render-service>.onrender.com/download.php?file=all.zip';
    } else if (currentFileId !== null) {
        window.location.href = `https://<your-render-service>.onrender.com/download.php?file=file${currentFileId}.pdf`;
    }
} else {
    loginStatus.style.color = 'red';
    loginStatus.textContent = textResult;
}