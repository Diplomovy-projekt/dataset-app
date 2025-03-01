import './bootstrap';



window.tryPrivateImage = function(img) {
    if (!img.dataset.failed) {
        console.log(`Public image failed for ${img.src}, trying private...`);
        img.dataset.failed = true;
        img.src = img.dataset.privateSrc;
    } else {
        console.log(`Private image also failed for ${img.dataset.privateSrc}`);
    }
}
