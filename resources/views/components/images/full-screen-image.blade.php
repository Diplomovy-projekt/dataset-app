<div x-cloak x-data="fullScreenImage"
     @keydown.escape.window="close()"
     @open-full-screen-image.window="prepareImgAndOverlay($event.detail.src, $event.detail.overlayId ?? null);"
>
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50"
        role="dialog"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>

        <div class="relative h-full w-full flex items-center justify-center">
            <button
                @click="close()"
                class="absolute top-4 right-4 z-50 p-2 rounded-full bg-black/50 hover:bg-black/70 transition-colors text-white"
                aria-label="Close modal"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div @click.outside="close()"
                 class="relative max-w-[90vw] max-h-[90vh]">
                <img
                    x-show="imageSrc"
                    :src="imageSrc"
                    alt="Full Screen Image"
                    class="max-w-full max-h-[90vh] object-contain"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                >
                <div x-html="overlayNode" class="absolute inset-0 pointer-events-none"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('fullScreenImage', () => ({
            imageSrc: '',
            overlayNode: '',
            isOpen: false,
            close() {
                this.imageSrc = '';
                this.overlayNode = '';
                this.isOpen = false;
            },
            prepareImgAndOverlay(imageSrc, overlayId = null) {
                if (imageSrc.includes('/thumbnails/')) {
                    imageSrc = imageSrc.replace('/thumbnails/', '/full-images/');
                } else if (imageSrc.includes('/private-image/')) {
                    const urlParts = imageSrc.split('/');
                    let encodedFilename = urlParts.pop();
                    let dataset = urlParts.pop();

                    try {
                        let decodedFilename = atob(encodedFilename);
                        let updatedFilename = decodedFilename.replace(/\/?thumbnails\/?/g, '/full-images/');

                        if (updatedFilename !== decodedFilename) {
                            encodedFilename = btoa(updatedFilename);
                            imageSrc = `${urlParts.join('/')}/${dataset}/${encodedFilename}`;
                        }
                    } catch (error) {
                        console.error("Error decoding filename:", error);
                    }
                }

                this.imageSrc = imageSrc;
                this.overlayNode = overlayId ? document.getElementById(overlayId)?.outerHTML || '' : '';
                this.isOpen = true;
            }




        }));
    });
</script>
