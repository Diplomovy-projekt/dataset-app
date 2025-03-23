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
            <!-- Zoom controls -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 z-50 flex items-center bg-black/50 rounded-full p-1">
                <button
                    @click="zoomOut()"
                    class="p-2 text-white hover:bg-black/70 rounded-full transition-colors"
                    aria-label="Zoom out"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <span class="text-white mx-2" x-text="`${Math.round(zoom * 100)}%`"></span>
                <button
                    @click="zoomIn()"
                    class="p-2 text-white hover:bg-black/70 rounded-full transition-colors"
                    aria-label="Zoom in"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
                <button
                    @click="resetZoom()"
                    class="p-2 text-white hover:bg-black/70 rounded-full transition-colors ml-2"
                    aria-label="Reset zoom"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
                    </svg>
                </button>
            </div>

            <button
                @click="close()"
                class="absolute top-4 right-4 z-50 p-2 rounded-full bg-black/50 hover:bg-black/70 transition-colors text-white"
                aria-label="Close modal"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div
                class="relative overflow-visible"
                x-init="initWheelListener()"
                :style="`max-width: ${zoom <= 1 ? '90vw' : 'none'}; max-height: ${zoom <= 1 ? '90vh' : 'none'};`"
            >
                <div
                    x-show="imageSrc"
                    class="transform-origin-center cursor-move overflow-visible"
                    :style="`transform: translate(${panX}px, ${panY}px) scale(${zoom}); transition: transform ${isZooming ? '0s' : '0.3s'};`"
                    x-init="initTouchListeners($el)"
                    @mousedown="startPan($event)"
                    @mousemove="pan($event)"
                    @mouseup="endPan()"
                    @mouseleave="endPan()"
                    @dblclick="toggleZoom($event)"
                >
                    <img
                        :src="imageSrc"
                        alt="Full Screen Image"
                        class="rounded-lg"
                        :class="zoom <= 1 ? 'max-w-full max-h-[90vh] object-contain' : ''"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        draggable="false"
                        @load="imageLoaded($event)"
                    >
                    <div x-html="overlayNode" class="absolute inset-0 pointer-events-none"></div>
                </div>
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
            zoom: 1,
            minZoom: 0.5,
            maxZoom: 5,
            zoomStep: 0.2,
            panX: 0,
            panY: 0,
            isPanning: false,
            startX: 0,
            startY: 0,
            lastX: 0,
            lastY: 0,
            isZooming: false,
            imageWidth: 0,
            imageHeight: 0,
            containerWidth: 0,
            containerHeight: 0,
            wheelListener: null,
            touchStartListener: null,
            touchMoveListener: null,
            touchEndListener: null,

            // Initialize the wheel event listener with passive: false safely
            initWheelListener() {
                const container = this.$el;

                // Remove existing listener if any
                if (this.wheelListener) {
                    container.removeEventListener('wheel', this.wheelListener);
                }

                // Create the wheel event handler
                this.wheelListener = (event) => {
                    event.preventDefault();
                    this.handleWheel(event);
                };

                // Add the event listener with { passive: false }
                container.addEventListener('wheel', this.wheelListener, { passive: false });
            },

            // Initialize touch event listeners with appropriate passive settings
            initTouchListeners(element) {
                // Remove existing listeners if any
                if (this.touchStartListener) {
                    element.removeEventListener('touchstart', this.touchStartListener);
                    element.removeEventListener('touchmove', this.touchMoveListener);
                    element.removeEventListener('touchend', this.touchEndListener);
                }

                // Create touch event handlers
                this.touchStartListener = (event) => {
                    this.startPan(event);
                };

                this.touchMoveListener = (event) => {
                    if (this.isPanning) {
                        event.preventDefault();
                        this.pan(event);
                    }
                };

                this.touchEndListener = () => {
                    this.endPan();
                };

                // Add event listeners with appropriate passive settings
                element.addEventListener('touchstart', this.touchStartListener, { passive: true });
                element.addEventListener('touchmove', this.touchMoveListener, { passive: false });
                element.addEventListener('touchend', this.touchEndListener, { passive: true });
            },

            close() {
                this.imageSrc = '';
                this.overlayNode = '';
                this.isOpen = false;
                this.resetZoom();
            },

            resetZoom() {
                this.zoom = 1;
                this.panX = 0;
                this.panY = 0;
            },

            imageLoaded(event) {
                // Store original image dimensions for calculations
                this.imageWidth = event.target.naturalWidth;
                this.imageHeight = event.target.naturalHeight;

                // Store viewport dimensions
                this.viewportWidth = window.innerWidth * 0.9;
                this.viewportHeight = window.innerHeight * 0.9;

                // Calculate initial container dimensions based on image size and viewport
                this.containerWidth = Math.min(this.viewportWidth, this.imageWidth);
                this.containerHeight = Math.min(this.viewportHeight, this.imageHeight);
            },

            constrainPan() {
                // When zoomed out, no panning
                if (this.zoom <= 1) {
                    this.panX = 0;
                    this.panY = 0;
                    return;
                }

                // Calculate zoomed dimensions
                const scaledWidth = this.imageWidth * this.zoom;
                const scaledHeight = this.imageHeight * this.zoom;

                // Calculate boundaries based on how much larger the zoomed image is compared to viewport
                const overflowX = Math.max(0, (scaledWidth - this.viewportWidth) / 2);
                const overflowY = Math.max(0, (scaledHeight - this.viewportHeight) / 2);

                // Constrain pan values to prevent image from moving too far off screen
                this.panX = Math.min(overflowX, Math.max(-overflowX, this.panX));
                this.panY = Math.min(overflowY, Math.max(-overflowY, this.panY));
            },

            zoomIn() {
                this.isZooming = true;
                const prevZoom = this.zoom;
                this.zoom = Math.min(this.maxZoom, this.zoom + this.zoomStep);

                // Adjust pan to maintain position relative to center
                this.adjustPanForZoom(prevZoom);

                setTimeout(() => {
                    this.isZooming = false;
                }, 50);
            },

            zoomOut() {
                this.isZooming = true;
                const prevZoom = this.zoom;
                this.zoom = Math.max(this.minZoom, this.zoom - this.zoomStep);

                // Adjust pan to maintain position relative to center
                this.adjustPanForZoom(prevZoom);

                setTimeout(() => {
                    this.isZooming = false;
                }, 50);
            },

            adjustPanForZoom(prevZoom) {
                // Scale pan values proportionally to keep the same center point
                const scale = this.zoom / prevZoom;
                this.panX = this.panX * scale;
                this.panY = this.panY * scale;

                // Make sure we're within bounds
                this.constrainPan();
            },

            toggleZoom(event) {
                if (this.zoom === 1) {
                    // Zoom in to 2.5x at the point that was clicked
                    const prevZoom = this.zoom;
                    this.zoom = 2.5;

                    // Calculate the center point relative to the image
                    const rect = event.target.getBoundingClientRect();
                    const offsetX = event.clientX - rect.left;
                    const offsetY = event.clientY - rect.top;

                    // Calculate center of viewport
                    const viewportCenterX = window.innerWidth / 2;
                    const viewportCenterY = window.innerHeight / 2;

                    // Calculate difference from center
                    const diffX = offsetX - rect.width / 2;
                    const diffY = offsetY - rect.height / 2;

                    // Set pan to center clicked point
                    this.panX = -diffX * this.zoom;
                    this.panY = -diffY * this.zoom;

                    // Constrain to prevent image from moving outside viewable area
                    this.constrainPan();
                } else {
                    this.resetZoom();
                }
            },

            handleWheel(event) {
                this.isZooming = true;
                const prevZoom = this.zoom;

                // Get mouse position relative to the image for centered zooming
                const rect = event.target.getBoundingClientRect();
                const offsetX = event.clientX - rect.left - rect.width / 2;
                const offsetY = event.clientY - rect.top - rect.height / 2;

                // Determine zoom direction
                if (event.deltaY < 0) {
                    // Zoom in
                    this.zoom = Math.min(this.maxZoom, this.zoom + this.zoomStep);
                } else {
                    // Zoom out
                    this.zoom = Math.max(this.minZoom, this.zoom - this.zoomStep);
                }

                // Adjust pan to keep mouse position stable
                const scale = this.zoom / prevZoom;
                this.panX = this.panX * scale;
                this.panY = this.panY * scale;

                // Constrain boundaries
                this.constrainPan();

                setTimeout(() => {
                    this.isZooming = false;
                }, 50);
            },

            startPan(event) {
                if (this.zoom <= 1) return;

                this.isPanning = true;

                // Handle both mouse and touch events
                if (event.type === 'touchstart') {
                    this.startX = event.touches[0].clientX;
                    this.startY = event.touches[0].clientY;
                } else {
                    this.startX = event.clientX;
                    this.startY = event.clientY;
                }

                this.lastX = this.panX;
                this.lastY = this.panY;
            },

            pan(event) {
                if (!this.isPanning) return;

                let currentX, currentY;

                // Handle both mouse and touch events
                if (event.type === 'touchmove') {
                    currentX = event.touches[0].clientX;
                    currentY = event.touches[0].clientY;
                } else {
                    currentX = event.clientX;
                    currentY = event.clientY;
                }

                // Calculate the distance moved
                this.panX = this.lastX + (currentX - this.startX);
                this.panY = this.lastY + (currentY - this.startY);

                // Constrain pan to keep image visible
                this.constrainPan();
            },

            endPan() {
                this.isPanning = false;
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
                this.resetZoom();
            }
        }));
    });
</script>
