
<div class="mx-auto px-4 py-8">
    <div class="shadow-xl py-12">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-5xl font-extrabold text-gray-200 mb-8 tracking-tight leading-tight">
                Tailor Your Perfect Dataset
            </h2>
            <div class=" p-8  border-gray-700 mb-8">
                <p class="text-xl text-gray-200 leading-relaxed mb-6">
                    Create a dataset that aligns perfectly with your project’s unique needs. Our interactive builder empowers you to <span class="font-bold text-blue-500">customize</span> your data selection in a multi-step process, ensuring precision and relevance for your research or application.
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
        @foreach($stageData as $index => $stage)
            <div class="rounded-lg" wire:key="{{$index}}">
                <x-builder.main-accordion
                    stageIndex="{{ $index }}"
                    :currentStage="$currentStage"
                    :completedStages="$completedStages"
                    :stageData="$stageData">

                    @switch($index)
                        @case(1)
                            <x-builder.categories-stage :categories="$categories" />
                            @break

                        @case(2)
                            <x-builder.origin-stage :originData="$originData" />
                            @break

                        @case(3)
                            <x-builder.datasets-stage :datasets="$datasets" />
                            @break

                        @case(4)
                            <x-builder.classes-stage />
                            @break

                        @case(5)
                            <x-builder.final-stage />
                            @break

                        @case(6)
                            <x-builder.download-stage />
                            @break

                        @default
                            <p>Unknown Stage</p>
                    @endswitch
                </x-builder.main-accordion>
            </div>
        @endforeach
    </div>




    <div class=" mx-auto bg-gray-800 rounded-lg shadow-xl overflow-hidden">

        <!-- Step 1: Categories Selection -->
        <div class="p-8">
            <h2 class="text-2xl font-bold mb-6">Select Categories</h2>
            <div class="grid grid-cols-3 gap-4">
                <label class="bg-gray-700 p-4 rounded-lg hover:bg-gray-600 flex items-center">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600 mr-3">
                    <div>
                        <h3 class="text-lg font-semibold">Digits</h3>
                        <p class="text-gray-400">Numeric character recognition</p>
                    </div>
                </label>
                <label class="bg-gray-700 p-4 rounded-lg hover:bg-gray-600 flex items-center">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600 mr-3">
                    <div>
                        <h3 class="text-lg font-semibold">Letters</h3>
                        <p class="text-gray-400">Alphabetic character recognition</p>
                    </div>
                </label>
                <label class="bg-gray-700 p-4 rounded-lg hover:bg-gray-600 flex items-center">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600 mr-3">
                    <div>
                        <h3 class="text-lg font-semibold">Words</h3>
                        <p class="text-gray-400">Full word annotations</p>
                    </div>
                </label>
            </div>
            <div class="mt-6 text-right">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Next: Select Dataset Origin
                </button>
            </div>
        </div>

        <!-- Step 2: Dataset Origin -->
        <div class="p-8">
            <h2 class="text-2xl font-bold mb-6">Select Dataset Origin</h2>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Centuries</h3>
                    <div class="space-y-2">
                        <label class="flex items-center bg-gray-700 p-2 rounded">
                            <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 mr-3">
                            15th Century
                        </label>
                        <label class="flex items-center bg-gray-700 p-2 rounded">
                            <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 mr-3">
                            16th Century
                        </label>
                        <label class="flex items-center bg-gray-700 p-2 rounded">
                            <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 mr-3">
                            17th Century
                        </label>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Languages</h3>
                    <div class="space-y-2">
                        <label class="flex items-center bg-gray-700 p-2 rounded">
                            <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 mr-3">
                            Latin
                        </label>
                        <label class="flex items-center bg-gray-700 p-2 rounded">
                            <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 mr-3">
                            Greek
                        </label>
                        <label class="flex items-center bg-gray-700 p-2 rounded">
                            <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 mr-3">
                            Arabic
                        </label>
                    </div>
                </div>
            </div>
            <div class="mt-6 text-right">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Next: Select Datasets
                </button>
            </div>
        </div>

        <!-- Step 3: Datasets -->
        <div class="p-8">
            <h2 class="text-2xl font-bold mb-6">Select Datasets</h2>

            <div class="space-y-4">
                <div class="bg-gray-700 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xl font-semibold">Handwritten Digits Dataset</h3>
                            <p class="text-gray-400">Annotation: Bounding Box</p>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-300">Total Images: <span class="font-bold text-indigo-400">5,000</span></p>
                            <p class="text-gray-300">Classes: <span class="font-bold text-indigo-400">10</span></p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center mr-4">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600 mr-2">
                            Select Dataset
                        </label>
                        <button class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-500">
                            View Classes
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-right">
                <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Next: Final Selection
                </button>
            </div>
        </div>
        <div class="p-8">
            <h2 class="text-2xl font-bold mb-6">Final Dataset Composition</h2>

            <div class="bg-gray-700 rounded-lg p-6">
                <div class="grid grid-cols-5 gap-4">
                    <div class="relative">
                        <img src="https://picsum.photos/seed/final1/200/200" class="rounded-lg">
                        <input type="checkbox" class="absolute top-2 right-2 form-checkbox h-5 w-5 text-indigo-600">
                    </div>
                    <div class="relative">
                        <img src="https://picsum.photos/seed/final2/200/200" class="rounded-lg">
                        <input type="checkbox" class="absolute top-2 right-2 form-checkbox h-5 w-5 text-indigo-600">
                    </div>
                    <!-- More images -->
                </div>

                <div class="mt-6 bg-gray-800 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Dataset Statistics</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <p>Total Images: <span class="text-indigo-400">1,500</span></p>
                        <p>Classes: <span class="text-indigo-400">10</span></p>
                        <p>Image Dimensions: <span class="text-indigo-400">28x28 px</span></p>
                    </div>
                </div>

                <button class="mt-4 w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700">
                    Download Dataset
                </button>
            </div>
        </div>
        <!-- Modal for Classes (would be dynamically shown) -->
        <div class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 ">
            <div class="bg-gray-800 w-3/4 mx-auto my-12 rounded-lg p-8 max-h-[80vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Dataset Classes</h2>
                    <input type="text" placeholder="Search classes..." class="bg-gray-700 text-white px-4 py-2 rounded">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-700 p-4 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <h3>Digit: 0</h3>
                            <input type="checkbox" class="form-checkbox">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <img src="https://picsum.photos/seed/0/200/200" class="rounded">
                            <img src="https://picsum.photos/seed/1/200/200" class="rounded">
                        </div>
                    </div>
                    <!-- More class items -->
                </div>
            </div>
        </div>
    </div>
</div>
