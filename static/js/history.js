// History management functions
// Copy to clipboard
document.addEventListener('DOMContentLoaded', function() {
    // Copy individual links
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.onclick = function() {
            const link = btn.parentElement.querySelector('.history-link').value;
            navigator.clipboard.writeText(link);
            btn.textContent = '✔';
            setTimeout(()=>{btn.textContent='📋';}, 1000);
        };
    });

    // Remove from history
    document.querySelectorAll('.del-btn').forEach(btn => {
        btn.onclick = function() {
            const idx = btn.getAttribute('data-idx');
            const file = btn.getAttribute('data-file');
            window.location = '?del_history=' + idx + '&del_file=' + encodeURIComponent(file);
        };
    });
});
