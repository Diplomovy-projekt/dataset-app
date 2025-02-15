<div x-data="{
        message: null,
        type: null,
        show: false,
        timer: null,
        showNotification(data) {
            this.message = data.message;
            this.type = data.type;
            this.show = true;
            this.resetTimer();
        },
        closeNotification() {
            this.show = false;
            if (this.timer) clearTimeout(this.timer);
        },
        resetTimer() {
            if (this.timer) clearTimeout(this.timer);
            this.timer = setTimeout(() => this.show = false, 3000);
        }
    }"
     @flash-msg.window="showNotification($event.detail[0])"
     class="fixed top-4 right-4 z-50">

    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-8"
         x-transition:enter-end="opacity-100 transform translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-x-0"
         x-transition:leave-end="opacity-0 transform translate-x-8"
         :class="{
            'bg-green-500': type === 'success',
            'bg-red-500': type === 'error'
         }"
         class="p-4 rounded-lg text-white shadow-lg"
         @mouseenter="clearTimeout(timer)"
         @mouseleave="resetTimer()">

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <template x-if="type === 'success'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </template>
                <template x-if="type === 'error'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </template>
                <span x-text="message"></span>
            </div>
            <button @click="closeNotification()" class="text-white hover:text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
