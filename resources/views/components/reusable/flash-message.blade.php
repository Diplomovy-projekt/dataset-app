<div>
    <div id="flash-status" class="absolute inset-0 flex items-center justify-center pointer-events-none z-50">
        <div id="flasg-msg" class=" text-white p-2 rounded-md shadow-md text-sm">
        </div>

    </div>
    <script>
        Livewire.on('flash-message', message => {
            let msg = $("#flasg-msg");
            msg.text(message[0]['message']).fadeIn(500);

            msg.toggleClass('bg-green-500', message[0].success);
            msg.toggleClass('bg-red-500', !message[0].success);
            setTimeout(function() {
                msg.fadeOut('fast');
            }, 1000);
        });
    </script>
</div>
