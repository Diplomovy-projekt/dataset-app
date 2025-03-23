<div class="max-w-7xl mx-auto px-4 sm:px-6 ">
    <!-- Hero Section with gradient background -->
    <div class="rounded-2xl border border-slate-700 py-16">
        <div class="max-w-4xl mx-auto sm:px-6">
            <!-- Header -->
            <div class="text-center mb-12">
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-200 mb-6 tracking-tight leading-tight">
                    Tailor Your Perfect Dataset
                </h2>
                <div class=" rounded-xl p-3 sm:p-8 backdrop-blur-sm mb-8">
                    <p class="text-lg sm:text-xl text-gray-200 leading-relaxed">
                        Create a dataset that aligns perfectly with your project's unique needs. Our interactive builder empowers you to
                        <span class="text-blue-400 font-semibold">customize</span>
                        your data selection in a multi-step process, ensuring precision and relevance for your research or application.
                    </p>
                </div>

                <!-- Info Card -->
                <div class="bg-slate-800/40 border-l-4 border-blue-500 p-6 rounded-lg mb-10">
                    <div class="flex items-center justify-center gap-3 text-gray-200">
                        <div class="bg-blue-500/20 p-2 rounded-lg">
                            <x-icon name="o-information-circle" class="w-5 h-5 text-blue-400" />
                        </div>
                        <p class="text-lg">Build a dataset that's as unique as your project.</p>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="flex justify-center">
                    <button
                        wire:click="nextStage"
                        @click="document.getElementById('builder').scrollIntoView({ behavior: 'smooth' }); clicked = true;"
                        x-data="{ clicked: false }"
                        :disabled="clicked"
                        class="group relative px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-500 rounded-xl font-semibold text-gray-200 transition-all duration-300 hover:shadow-[0_0_20px_rgba(59,130,246,0.3)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none"
                        :class="{ 'opacity-50 cursor-not-allowed': clicked }">
                        <div class="flex items-center gap-3">
                            <span>Start Building</span>
                            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Builder Section -->
    <div id="builder" class="mt-12 space-y-4">
        @foreach($stageData as $stageNumber => $stage)

            <div id="{{$stage['method']}}" class="rounded-xl  " wire:key="livewire-builder-{{$stageNumber}}">
                <x-builder.main-accordion
                    stageIndex="{{ $stageNumber }}"
                    :currentStage="$currentStage"
                    :completedStages="$completedStages"
                    :stageData="$stageData">

                    @if($currentStage == $stageNumber)
                        <x-dynamic-component :component="$stage['component']"/>
                    @endif
                </x-builder.main-accordion>
            </div>
        @endforeach
    </div>
</div>
