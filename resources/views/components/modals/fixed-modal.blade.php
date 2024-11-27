<div x-cloak x-show="open === '{{ $modalId }}'" id="modal-outter"
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 flex items-center justify-center z-30 w-full h-full overflow-hidden bg-black bg-opacity-50 backdrop-blur-sm">
    <div id="modalContent"
         class="relative z-40 text-white bg-slate-800 my-10 mx-auto w-auto  max-h-[90%] overflow-auto rounded shadow-lg p-2 sm:p-5 pt-10 box-border"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="scale-50 opacity-0"
         x-transition:enter-end="scale-100 opacity-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="scale-100 opacity-100"
         x-transition:leave-end="scale-50 opacity-0">
        <!-- Close button in the right upper corner -->
        <button @click="open = ''" class="absolute top-2 right-2 text-gray-400 hover:text-white">
            <x-icon name="o-x-mark" class="w-6 h-6" />
        </button>

        {{ $slot }}
    </div>
</div>
