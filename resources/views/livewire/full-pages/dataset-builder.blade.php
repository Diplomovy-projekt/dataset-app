
<div class="mx-auto px-4 py-8">
    <div class="shadow-xl py-12">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-5xl font-extrabold text-gray-200 mb-8 tracking-tight leading-tight">
                Tailor Your Perfect Dataset
            </h2>
            <div class=" p-8  border-gray-700 mb-8">
                <p class="text-xl text-gray-200 leading-relaxed mb-6">
                    Create a dataset that aligns perfectly with your project’s unique needs. Our interactive builder empowers you to
                    <span class="font-bold text-blue-500">customize</span>
                    your data selection in a multi-step process, ensuring precision and relevance for your research or application.
                </p>
            </div>
            <div class="bg-blue-500 bg-opacity-20 border-l-4 border-blue-500 p-6 rounded-r-lg mb-8 shadow-md">
                <p class="text-blue-300 text-lg flex items-center justify-center">
                    <svg class="w-6 h-6 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Build a dataset that’s as unique as your project.
                </p>
            </div>

            <div class="flex justify-center">
                <button
                    wire:click="nextStage"
                    @click="document.getElementById('builder').scrollIntoView({ behavior: 'smooth' }); clicked = true;"
                    x-data="{ clicked: false }"
                    :disabled="clicked"
                    :class="{ 'opacity-50 cursor-not-allowed': clicked }"
                    class="bg-blue-500 text-gray-200 font-bold py-4 px-10 rounded-lg hover:bg-blue-600 transition duration-300 ease-in-out transform hover:scale-105 flex items-center space-x-3 shadow-lg">
                    <span>Start Building</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="builder" class="flex flex-col mx-auto py-4 gap-1 my-10">
        @foreach($stageData as $stageNumber => $stage)
            <div class="rounded-lg" wire:key="{{$stageNumber}}">
                <x-builder.main-accordion
                    stageIndex="{{ $stageNumber }}"
                    :currentStage="$currentStage"
                    :completedStages="$completedStages"
                    :stageData="$stageData">

                    @if(in_array($stageNumber, $completedStages) || $currentStage == $stageNumber)
                        @switch($stageNumber)
                            @case(1)
                                <x-builder.categories-stage :categories="$categories" />
                                @break

                            @case(2)
                                <x-builder.origin-stage :metadataValues="$metadataValues" />
                                @break

                            @case(3)
                                <x-builder.datasets-stage :datasets="$datasets" />
                                @break

                            @case(9)
                                <x-builder.classes-stage />
                                @break

                            @case(4)
                                <x-builder.final-stage />
                                @break

                            @case(5)
                                <x-builder.download-stage />
                                @break

                            @default
                                <p>Unknown Stage</p>
                        @endswitch
                    @endif
                </x-builder.main-accordion>
            </div>
        @endforeach
    </div>
</div>
