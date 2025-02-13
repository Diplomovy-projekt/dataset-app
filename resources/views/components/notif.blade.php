{{--
<div>
    <div id="flash-status" class="absolute inset-0 flex items-center justify-center pointer-events-none z-50">
        <div id="flasg-msg" class=" text-white rounded-md shadow-md text-sm">
        </div>

    </div>
    <script>
        Livewire.on('flash-message', message => {
            let msg = $("#flasg-msg");
            msg.addClass('p-2')
            msg.text(message[0]['message']).fadeIn(500);

            msg.toggleClass('bg-green-500', message[0].success);
            msg.toggleClass('bg-red-500', !message[0].success);

            setTimeout(function() {
                msg.fadeOut('fast');
            }, 1000);
            msg.removeClass('p-2')
        });
    </script>
</div>
--}}

<div x-data="{
    notifications: [],
    add(message) {
        console.log('message', message);
        this.notifications.push({
            id: Date.now(),
            type: message.type,
            message: message.message
        });
        //setTimeout(() => this.remove(this.notifications[0].id), 3000);
    },
    remove(id) {
        this.notifications = this.notifications.filter(notification => notification.id !== id);
    }
}"
     @flash-msg.window="add($event.detail)"
     class="fixed top-4 right-4 z-50">

    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="true"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-8"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-8"
             :class="{
                'bg-green-500': notification.type === 'success',
                'bg-red-500': notification.type === 'error',
                'bg-yellow-500': notification.type === 'warning'
             }"
             class="mb-4 p-4 rounded-lg text-white shadow-lg">
            <div class="flex items-center gap-3">
                <template x-if="notification.type === 'success'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </template>
                <template x-if="notification.type === 'error'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </template>
                <span x-text="notification.message"></span>
            </div>
        </div>
    </template>
</div>
