<div x-cloak
    x-data="{
        images: [],
        overlayNode: '',
        currentIndex: 0,
        isOpen: false,
        next() {
            this.currentIndex = this.currentIndex === this.images.length - 1 ? 0 : this.currentIndex + 1;
        },
        previous() {
            this.currentIndex = this.currentIndex === 0 ? this.images.length - 1 : this.currentIndex - 1;
        },
        close() {
            this.images = [];
            this.overlayNode = '';
            this.isOpen = false;
        },
        prepareImgAndOverlay(imageSrc, overlayId = null) {
            if (overlayId) {
                const overlayElement = document.getElementById(overlayId);
                if (overlayElement) {
                    this.overlayNode = overlayElement.cloneNode(true).outerHTML;
                }
            } else{
                this.overlayNode = '';
            }
            this.images.push(imageSrc);
            this.isOpen = true;
        }
    }"
    @keydown.right.window="next()"
    @keydown.left.window="previous()"
    @keydown.escape.window="close()"
    @open-full-screen-image.window="prepareImgAndOverlay($event.detail.src, $event.detail.overlayId ?? null);"
>

    {{-- Modal --}}
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
        {{-- Backdrop --}}
        <div
            class="fixed inset-0 bg-black/80 backdrop-blur-sm"
        ></div>

        {{-- Modal Content --}}
        <div class="relative h-full w-full flex items-center justify-center">
            {{-- Close Button --}}
            <button
                @click="close()"
                class="absolute top-4 right-4 z-50 p-2 rounded-full bg-black/50 hover:bg-black/70 transition-colors text-white"
                aria-label="Close modal"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            {{-- Image Container --}}
            <div class="relative max-w-[90vw] max-h-[90vh]">
                <template x-for="(image, index) in images" :key="index">
                    <img
                        x-show="currentIndex === index"
                        :src="image"
                        :alt="`Image ${index + 1}`"
                        class="max-w-full max-h-[90vh] object-contain"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                    >
                </template>
                <div x-html="overlayNode" class="absolute inset-0 pointer-events-none"></div>
            </div>
            {{-- Navigation Arrows (only shown if multiple images) --}}
            <template x-if="images.length > 1">
                <div>
                    {{-- Previous Button --}}
                    <button
                        @click="previous()"
                        class="absolute left-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/50 hover:bg-black/70 transition-colors text-white"
                        aria-label="Previous image"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>

                    {{-- Next Button --}}
                    <button
                        @click="next()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-full bg-black/50 hover:bg-black/70 transition-colors text-white"
                        aria-label="Next image"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    {{-- Image Counter --}}
                    <div
                        class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/50 px-3 py-1 rounded-full text-white text-sm"
                        x-text="`${currentIndex + 1} / ${images.length}`"
                    ></div>
                </div>
            </template>
        </div>
    </div>
</div>
