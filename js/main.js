// Paste screenshot support
const fileInput = document.getElementById('fileInput');
const uploadForm = document.getElementById('uploadForm');
const preview = document.getElementById('preview');
const dragIndicator = document.getElementById('dragIndicator');

const progressBar = document.createElement('div');
progressBar.id = 'uploadProgressBar';
progressBar.style = 'font-family: monospace; color: #8ab4f8; margin: 18px 0 12px 0; font-size: 1.1em;';
progressBar.hidden = true;
preview.parentNode.insertBefore(progressBar, preview);

const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100 MB

function showProgress(percent, currentFile, totalFiles) {
    if (percent === 100) {
        if (currentFile === totalFiles) {
            progressBar.textContent = 'Done!';
            progressBar.hidden = false;
            progressBar.classList.add('progress-glow');
            setTimeout(hideProgress, 1200);
            return;
        } else {
            progressBar.textContent = `File ${currentFile + 1}/${totalFiles} completed`;
            return;
        }
    }
    let dotsCount = Math.floor(percent / 10);
    let text = `Uploading file ${currentFile}/${totalFiles}`;
    for (let i = 1; i <= dotsCount; i++) {
        text += '.';
        if (i === 5) text += '50%';
        if (i === 10) text += '100%';
    }
    progressBar.textContent = text;
    progressBar.hidden = false;
    progressBar.classList.add('progress-glow');
}

function hideProgress() {
    progressBar.hidden = true;
    progressBar.textContent = '';
    progressBar.style.color = '#8ab4f8';
    progressBar.classList.remove('progress-glow');
}

function showFileTooLarge() {
    progressBar.style.color = 'red';
    progressBar.textContent = 'File is too large (max 100 MB).';
    progressBar.hidden = false;
    setTimeout(hideProgress, 2000);
}

function showDragIndicator() {
    if (dragIndicator) dragIndicator.classList.add('show');
}

function hideDragIndicator() {
    if (dragIndicator) dragIndicator.classList.remove('show');
}

function uploadMultipleFiles(files) {
    console.log('uploadMultipleFiles called with:', files);
    const formData = new FormData();
    let totalSize = 0;
    
    // Проверяем размер всех файлов
    for (let i = 0; i < files.length; i++) {
        if (files[i].size > MAX_FILE_SIZE) {
            showFileTooLarge();
            return;
        }
        totalSize += files[i].size;
        formData.append('files[]', files[i]);
    }
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href);
    
    let currentFile = 1;
    let totalFiles = files.length;
    
    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            let percent = Math.round((e.loaded / e.total) * 100);
            showProgress(percent, currentFile, totalFiles);
        }
    };
    
    xhr.onload = function() {
        hideProgress();
        if (xhr.status === 200) {
            // Добавляем анимацию успеха перед перезагрузкой
            document.body.classList.add('upload-success');
            setTimeout(() => {
                window.location.reload();
            }, 800);
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
        uploadMultipleFiles(Array.from(fileInput.files));
    }
    return false;
};

// Drag&Drop и Paste — тоже через uploadMultipleFiles
fileInput.addEventListener('change', function() {
    let label = document.getElementById('fileLabelText');
    if (fileInput.files.length) {
        const files = Array.from(fileInput.files);
        let hasLargeFile = false;
        
        // Проверяем размер всех файлов
        for (let i = 0; i < files.length; i++) {
            if (files[i].size > MAX_FILE_SIZE) {
                showFileTooLarge();
                fileInput.value = '';
                label.textContent = 'Choose files';
                preview.innerHTML = '';
                return;
            }
        }
        
        if (files.length === 1) {
            label.textContent = files[0].name;
        } else {
            label.textContent = `${files.length} files selected`;
        }
        
        preview.innerHTML = '';
        
        // Показываем превью для изображений с анимацией
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'file-preview';
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                img.style.margin = '5px';
                img.style.borderRadius = '8px';
                img.style.border = '1px solid #444';
                img.style.animationDelay = `${index * 0.1}s`;
                img.style.opacity = '0';
                img.style.transform = 'translateY(20px)';
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
                
                // Анимация появления с задержкой
                setTimeout(() => {
                    img.style.transition = 'all 0.3s ease';
                    img.style.opacity = '1';
                    img.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
        
        uploadMultipleFiles(files);
    } else {
        label.textContent = 'Choose files';
        preview.innerHTML = '';
    }
});

document.addEventListener('paste', function (event) {
    const items = (event.clipboardData || event.originalEvent.clipboardData).items;
    const files = [];
    
    for (let i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') !== -1) {
            const blob = items[i].getAsFile();
            if (blob.size > MAX_FILE_SIZE) {
                showFileTooLarge();
                return;
            }
            files.push(blob);
        }
    }
    
    if (files.length > 0) {
        const dt = new DataTransfer();
        files.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
        
        // Show preview with animation
        preview.innerHTML = '';
        files.forEach((file, index) => {
            const img = document.createElement('img');
            img.className = 'file-preview';
            img.style.maxWidth = '100px';
            img.style.maxHeight = '100px';
            img.style.margin = '5px';
            img.style.borderRadius = '8px';
            img.style.border = '1px solid #444';
            img.style.animationDelay = `${index * 0.1}s`;
            img.style.opacity = '0';
            img.style.transform = 'translateY(20px)';
            img.src = URL.createObjectURL(file);
            preview.appendChild(img);
            
            // Анимация появления с задержкой
            setTimeout(() => {
                img.style.transition = 'all 0.3s ease';
                img.style.opacity = '1';
                img.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        uploadMultipleFiles(files);
    }
});

// Drag&Drop upload
window.addEventListener('drop', function(e) {
    e.preventDefault();
    document.body.classList.remove('body-dragover');
    hideDragIndicator();
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
        const files = Array.from(e.dataTransfer.files);
        console.log('Dropped files:', files);
        let hasLargeFile = false;
        for (let i = 0; i < files.length; i++) {
            if (files[i].size > MAX_FILE_SIZE) {
                showFileTooLarge();
                fileInput.value = '';
                let label = document.getElementById('fileLabelText');
                label.textContent = 'Choose files';
                preview.innerHTML = '';
                return;
            }
        }
        const dt = new DataTransfer();
        files.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
        let label = document.getElementById('fileLabelText');
        if (files.length === 1) {
            label.textContent = files[0].name;
        } else {
            label.textContent = `${files.length} files selected`;
        }
        preview.innerHTML = '';
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'file-preview';
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                img.style.margin = '5px';
                img.style.borderRadius = '8px';
                img.style.border = '1px solid #444';
                img.style.animationDelay = `${index * 0.1}s`;
                img.style.opacity = '0';
                img.style.transform = 'translateY(20px)';
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
                setTimeout(() => {
                    img.style.transition = 'all 0.3s ease';
                    img.style.opacity = '1';
                    img.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
        uploadMultipleFiles(files);
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
    showDragIndicator();
});
window.addEventListener('dragleave', function(e) {
    if (e.target === document.body) {
        document.body.classList.remove('body-dragover');
        hideDragIndicator();
    }
});
// История: copy и delete
if (document.querySelectorAll('.copy-btn').length) {
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.onclick = function() {
            const link = btn.getAttribute('data-link') || btn.parentElement.querySelector('.history-link').value;
            navigator.clipboard.writeText(link);
            btn.textContent = '✔';
            setTimeout(()=>{btn.textContent='📋';}, 1000);
        };
    });
}

// Копирование всех ссылок
if (document.getElementById('copyAllBtn')) {
    document.getElementById('copyAllBtn').onclick = function() {
        const links = [];
        document.querySelectorAll('.link a[href*="' + window.location.host + '"]').forEach(link => {
            links.push(link.href);
        });
        
        if (links.length > 0) {
            navigator.clipboard.writeText(links.join('\n'));
            this.textContent = '✔ Copied!';
            this.style.background = '#4CAF50';
            setTimeout(() => {
                this.textContent = '📋 Copy all links';
                this.style.background = '#5865f2';
            }, 2000);
        }
    };
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