// Paste screenshot support
const fileInput = document.getElementById('fileInput');
const uploadForm = document.getElementById('uploadForm');
const preview = document.getElementById('preview');

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
            // Auto submit
            setTimeout(() => uploadForm.submit(), 100);
        }
    }
});
fileInput.addEventListener('change', function() {
    let label = document.getElementById('fileLabelText');
    if (fileInput.files.length) {
        label.textContent = fileInput.files[0].name;
    } else {
        label.textContent = 'Choose file';
    }
    preview.innerHTML = '';
    if (fileInput.files.length && fileInput.files[0].type.startsWith('image/')) {
        const img = document.createElement('img');
        img.style.maxWidth = '100%';
        img.style.maxHeight = '200px';
        img.src = URL.createObjectURL(fileInput.files[0]);
        preview.appendChild(img);
    }
    if (fileInput.files.length) {
        setTimeout(() => uploadForm.submit(), 100);
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
        setTimeout(() => uploadForm.submit(), 100);
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