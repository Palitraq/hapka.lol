// Modal management functions
document.addEventListener('DOMContentLoaded', function() {
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

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        let modal = document.getElementById('terms-modal');
        if (modal && event.target === modal) modal.style.display = 'none';
        let modal2 = document.getElementById('support-modal');
        if (modal2 && event.target === modal2) modal2.style.display = 'none';
    });
});
