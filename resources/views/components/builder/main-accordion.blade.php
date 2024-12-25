@props([
    'stageIndex',
    'stageTitle',
    'currentStage',
    'completedStages' => [],
    'stageData'
])

<div class="rounded-lg {{ $currentStage == $stageIndex ? 'mb-2' : 'mb-0' }} ">
    <div class=" rounded-t-lg p-4 py-5 {{ $currentStage == $stageIndex ? 'pb-1 border-x border-t border-gray-700' : 'border border-gray-700' }} bg-gray-900">
        <h3 class="flex font-bold {{ $currentStage == $stageIndex ? 'text-gray-200 text-2xl' : 'text-gray-500 text-base' }}">
            @if(in_array($stageIndex, $completedStages))
                <x-tni-tick-circle-o class="w-7 pr-2 text-green-500"/>
            @elseif(!in_array($stageIndex, $completedStages) && $currentStage != $stageIndex)
                <x-tni-lock-circle-o class="w-7 pr-2 text-gray-500"/>
            @endif
            Stage {{$stageIndex}}: {{ $stageData[$stageIndex]['title'] ?? " " }}
        </h3>
    </div>

    @if($currentStage == $stageIndex)
        <div class="p-4 pt-1 shadow-md bg-gray-900 rounded-b-lg border-x border-b border-gray-700">
            <hr class="border border-gray-700">
            <p class="text-gray-400 pb-4">{{ $stageData[$stageIndex]['description'] ?? " " }}</p>
            {{ $slot }}

            <hr class="border border-gray-700 mt-2">
            <div wire:loading.class="opacity-50 cursor-not-allowed"
                 wire:loading.attr="disabled"
                 wire:target="previousStage,nextStage"
                 class="mt-4 grid grid-cols-3 relative">
                <!-- Left Column -->
                <div>
                    @if($currentStage > 1)
                        <button wire:click="previousStage"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50"
                                wire:target="previousStage,nextStage"
                                class="bg-gray-300 text-black pr-4 pl-2 py-2 rounded-lg flex">
                            <x-bi-arrow-up-circle class="w-6 h-6 pr-1" />
                            Previous Stage
                        </button>
                    @endif
                </div>

                <!-- Middle Column - Loading Indicator (Absolute Positioning) -->
                <div wire:loading.class.remove="hidden"
                     wire:target="previousStage,nextStage"
                     class="hidden absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                    <div class="flex items-center whitespace-nowrap">
                        <x-css-spinner class="w-6 h-6 text-gray-500 animate-spin" />
                        <span class="ml-2 text-gray-500">Customizing your dataset</span>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-start-3 flex justify-end">
                    @if($currentStage < max(array_keys($stageData)))
                        <button wire:click="nextStage"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50"
                                wire:target="previousStage,nextStage"
                                class="bg-blue-500 text-white pr-4 pl-2 py-2 rounded-lg flex">
                            <x-bi-arrow-down-circle class="w-6 h-6 pr-1"/>
                            Next Stage
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
