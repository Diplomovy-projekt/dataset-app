<div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
    <?php echo __DIR__; echo $_SERVER['SERVER_NAME'];phpinfo(); ?>
    <!-- Counter Card -->
    <div class="card w-full max-w-md bg-white shadow-lg rounded-lg p-4 sm:p-6">
        <h1 class="text-xl font-semibold text-center mb-4">Livewire Counter</h1>
        <p class="text-center text-2xl font-mono mb-6">Count: <span class="text-blue-600 font-bold">{{ $count }}</span></p>

        <!-- Alpine.js example: Toggle Message on Button Click -->
        <div x-data="{ open: false }" class="flex flex-col gap-4 justify-center">
            <!-- Toggle the message with Alpine.js -->
            <div class="flex gap-4">

            <button wire:click="decrement" class="btn btn-outline btn-error w-32">Decrement</button>
            <button wire:click="increment" class="btn bg-green-500 text-white w-32">Increment</button>

            <!-- Button to toggle visibility with Alpine.js -->
            <button @click="open = !open" class="btn btn-secondary w-32">Toggle Message</button>
            </div>


            <!-- Conditional Message Display using Alpine.js -->
            <div x-show="open" x-transition class="mt-4 text-center text-lg">
                <p class="text-green-600">This is a toggled message!</p>
            </div>
        </div>
    </div>
</div>
