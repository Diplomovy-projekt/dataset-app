<div>
    <div x-data="{ isOpen: true }" class="my-4 bg-slate-800/50 border border-red-500/50 rounded-lg shadow-lg overflow-hidden">
        {{-- Clickable Error Header --}}
        <div @click="isOpen = !isOpen"
             class="px-4 py-3 bg-slate-800/80 border-b border-red-500/50 flex items-center justify-between cursor-pointer hover:bg-slate-800/90 transition-colors">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                          clip-rule="evenodd" />
                </svg>
                <h3 class="font-medium text-red-400">{{ $this->errors['message'] }}</h3>
            </div>

            {{-- Toggle Arrow --}}
            <svg class="w-5 h-5 text-red-400 transform transition-transform duration-200"
                 :class="{'rotate-180': !isOpen}"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        {{-- Collapsible Error Details --}}
        @if(isset($this->errors['data']) && count($this->errors['data']) > 0)
            <div x-show="isOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="px-4 py-3">
                <ul class="space-y-2">
                    @foreach($this->errors['data'] as $error)
                        <li class="flex items-center text-slate-300 text-sm">
                            <span class="mr-2">•</span>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
