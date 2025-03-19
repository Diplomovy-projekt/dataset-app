<div>
    <x-modals.fixed-modal modalId="resolve-request" class="w-full sm:w-fit">
        @if($request)
            <div class="p-2 sm:p-6 sm:w-[38rem] rounded-lg">
                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-xl font-semibold text-slate-200">Request Details</h2>
                </div>

                <!-- Request Details as Cards -->
                <div class="flex flex-col sm:grid sm:grid-cols-2 sm:gap-4 mb-5">
                    <!-- Dataset Card -->
                    <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700">
                        <div class="flex items-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            <h3 class="text-sm font-semibold text-slate-300">Dataset</h3>
                        </div>
                        <p class="text-base font-medium text-slate-200 truncate">{{ $request['dataset']['display_name'] ?? 'N/A' }}</p>
                    </div>

                    <!-- User Card -->
                    <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700">
                        <div class="flex items-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            <h3 class="text-sm font-semibold text-slate-300">Requested By</h3>
                        </div>
                        <p class="text-base font-medium text-slate-200 truncate">{{ $request['user']['email'] ?? 'N/A' }}</p>
                    </div>

                    <!-- Type Card -->
                    <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700">
                        <div class="flex items-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0L10 9.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            <h3 class="text-sm font-semibold text-slate-300">Request Type</h3>
                        </div>
                        @php
                            $color = match($request['type'] ?? '') {
                                'new' => 'green',
                                'extend' => 'blue',
                                'reduce' => 'yellow',
                                'delete' => 'red',
                                'edit' => 'purple',
                            };
                        @endphp

                        <x-misc.tag text="{{ ucfirst($request['type']) }} Dataset" color="{{ $color }}" />
                    </div>

                    <!-- Timestamp Card -->
                    <div class="bg-slate-800/40 p-4 rounded-lg border border-slate-700">
                        <div class="flex items-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            <h3 class="text-sm font-semibold text-slate-300">Requested At</h3>
                        </div>
                        <p class="text-base font-medium text-slate-200">
                            {{ isset($request['created_at']) ? \Carbon\Carbon::parse($request['created_at'])->format('M d, Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- Resolution Section -->
                <div class="bg-slate-800/30 p-4 rounded-lg border border-slate-700 mb-5">
                    <div class="flex items-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-7.536 5.879a1 1 0 001.415 0 3 3 0 014.242 0 1 1 0 001.415-1.415 5 5 0 00-7.072 0 1 1 0 000 1.415z" clip-rule="evenodd" />
                        </svg>
                        <h3 class="text-sm font-medium text-slate-300">Resolution Comments</h3>
                    </div>
                    <textarea wire:model="comment"
                              class="w-full p-3 bg-slate-800 text-slate-300 border border-slate-700/70 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 h-20"
                              placeholder="Add reason for approval or rejection (optional)..."></textarea>
                </div>

                <!-- Action Buttons with more distinction -->
                <div class="flex justify-between sm:items-center gap-2">
                    <div class="flex sm:flex-row flex-col items-start sm:items-center gap-2 sm:gap-0  sm:space-x-3">
                        <x-misc.button
                            wire:click="resolveRequest('approve')"
                            color="green"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Approve Request
                        </x-misc.button>
                        <x-misc.button
                            wire:click="resolveRequest('reject')"
                            color="red"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reject Request
                        </x-misc.button>
                    </div>
                    <x-misc.button
                        @click="open = ''"
                        color="gray"
                        class="hidden sm:block border border-slate-600 hover:bg-slate-700"
                    >
                        Close
                    </x-misc.button>
                </div>
            </div>
        @endif
    </x-modals.fixed-modal>
</div>
