// Paste screenshot support
const fileInput = document.getElementById('fileInput');
const uploadForm = document.getElementById('uploadForm');
const preview = document.getElementById('preview');

const progressBar = document.createElement('div');
progressBar.id = 'uploadProgressBar';
progressBar.style = 'font-family: monospace; color: #8ab4f8; margin: 18px 0 12px 0; font-size: 1.1em;';
progressBar.hidden = true;
preview.parentNode.insertBefore(progressBar, preview);

function showProgress(percent) {
    if (percent === 100) {
        progressBar.textContent = 'Done!';
        progressBar.hidden = false;
        setTimeout(hideProgress, 1200);
        return;
    }
    let dotsCount = Math.floor(percent / 10);
    let text = 'Loading';
    for (let i = 1; i <= dotsCount; i++) {
        text += '.';
        if (i === 5) text += '50%';
        if (i === 10) text += '100%';
    }
    progressBar.textContent = text;
    progressBar.hidden = false;
}
function hideProgress() {
    progressBar.hidden = true;
    progressBar.textContent = '';
}

function uploadWithProgress(file) {
    const formData = new FormData();
    formData.append('file', file);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href);
    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            let percent = Math.round((e.loaded / e.total) * 100);
            showProgress(percent);
        }
    };
    xhr.onload = function() {
        hideProgress();
        if (xhr.status === 200) {
            // Перезагрузить страницу для показа результата
            window.location.reload();
        } else {
            progressBar.textContent = 'Upload failed!';
        }
    };
    xhr.onerror = function() {
        hideProgress();
        progressBar.textContent = 'Upload error!';
    };
    xhr.send(formData);
}

// Переопределяем обработку формы
uploadForm.onsubmit = function(e) {
    e.preventDefault();
    if (fileInput.files.length) {
        uploadWithProgress(fileInput.files[0]);
    }
    return false;
};

// Drag&Drop и Paste — тоже через uploadWithProgress
fileInput.addEventListener('change', function() {
    let label = document.getElementById('fileLabelText');
    if (fileInput.files.length) {
        label.textContent = fileInput.files[0].name;
        preview.innerHTML = '';
        if (fileInput.files[0].type.startsWith('image/')) {
            const img = document.createElement('img');
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.src = URL.createObjectURL(fileInput.files[0]);
            preview.appendChild(img);
        }
        uploadWithProgress(fileInput.files[0]);
    } else {
        label.textContent = 'Choose file';
        preview.innerHTML = '';
    }
});

document.addEventListener('paste', function (event) {
    const items = (event.clipboardData || event.originalEvent.clipboardData).items;
    for (let i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') !== -1) {
            const blob = items[i].getAsFile();
            const dt = new DataTransfer();
            dt.items.add(blob);
            fileInput.files = dt.files;
            // Show preview
            const img = document.createElement('img');
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.src = URL.createObjectURL(blob);
            preview.innerHTML = '';
            preview.appendChild(img);
            uploadWithProgress(blob);
        }
    }
});

window.addEventListener('drop', function(e) {
    e.preventDefault();
    document.body.classList.remove('body-dragover');
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        let label = document.getElementById('fileLabelText');
        label.textContent = fileInput.files[0].name;
        preview.innerHTML = '';
        if (fileInput.files[0].type.startsWith('image/')) {
            const img = document.createElement('img');
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.src = URL.createObjectURL(fileInput.files[0]);
            preview.appendChild(img);
        }
        uploadWithProgress(fileInput.files[0]);
    }
});

// Support modal
if (document.querySelector('.support-btn')) {
    document.querySelector('.support-btn').onclick = function(e) {
        e.preventDefault();
        document.getElementById('support-modal').style.display = 'flex';
    };
    document.getElementById('support-close').onclick = function() {
        document.getElementById('support-modal').style.display = 'none';
    };
}
// Terms modal
if (document.getElementById('terms-link')) {
    document.getElementById('terms-link').onclick = function(e) {
        e.preventDefault();
        document.getElementById('terms-modal').style.display = 'flex';
    };
    document.getElementById('terms-close').onclick = function() {
        document.getElementById('terms-modal').style.display = 'none';
    };
}
window.addEventListener('click', function(event) {
    let modal = document.getElementById('terms-modal');
    if (modal && event.target === modal) modal.style.display = 'none';
    let modal2 = document.getElementById('support-modal');
    if (modal2 && event.target === modal2) modal2.style.display = 'none';
});
// Drag & Drop upload
window.addEventListener('dragover', function(e) {
    e.preventDefault();
    document.body.classList.add('body-dragover');
});
window.addEventListener('dragleave', function(e) {
    if (e.target === document.body) {
        document.body.classList.remove('body-dragover');
    }
});
// История: copy и delete
if (document.querySelectorAll('.copy-btn').length) {
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.onclick = function() {
            const link = btn.parentElement.querySelector('.history-link').value;
            navigator.clipboard.writeText(link);
            btn.textContent = '✔';
            setTimeout(()=>{btn.textContent='📋';}, 1000);
        };
    });
}
if (document.querySelectorAll('.del-btn').length) {
    document.querySelectorAll('.del-btn').forEach(btn => {
        btn.onclick = function() {
            const idx = btn.getAttribute('data-idx');
            const file = btn.getAttribute('data-file');
            window.location = '?del_history=' + idx + '&del_file=' + encodeURIComponent(file);
        };
    });
} 