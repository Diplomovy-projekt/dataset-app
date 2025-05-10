import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    window.tryPrivateImage = function(img) {
        if (!img.dataset.failed) {
            img.dataset.failed = true;
            img.src = img.dataset.privateSrc;
        }
    };
});
