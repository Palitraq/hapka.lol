document.addEventListener('DOMContentLoaded', function () {
    var img = document.querySelector('img.zoomable');
    if (!img) return;

    var isZoomed = false;

    function toggleZoom() {
        isZoomed = !isZoomed;
        if (isZoomed) {
            img.classList.add('is-zoomed');
            document.body.classList.add('no-scroll');
        } else {
            img.classList.remove('is-zoomed');
            document.body.classList.remove('no-scroll');
        }
    }

    img.addEventListener('click', toggleZoom);

    // Optional: zoom out on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isZoomed) {
            isZoomed = false;
            img.classList.remove('is-zoomed');
            document.body.classList.remove('no-scroll');
        }
    });
});


