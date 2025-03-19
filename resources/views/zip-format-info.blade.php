<x-app-layout>
    <div class=" min-h-screen text-gray-200 p-6">
        <div class="max-w-6xl mx-auto">
            <!-- Page Header -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-6 rounded-xl border border-slate-700 mb-8">
                <h1 class="text-3xl font-bold mb-2">Dataset Upload Guidelines</h1>
                <p class="text-slate-400">Learn how to structure your ZIP files for different annotation formats</p>
            </div>

            <!-- Format Selection Tabs -->
            <div class="mb-8" x-data="{ activeTab: 'coco' }">
                <div class="flex border-b border-slate-700 mb-6">
                    <button
                        @click="activeTab = 'coco'"
                        :class="activeTab === 'coco' ? 'border-blue-500 text-blue-500' : 'text-slate-400 hover:text-gray-200'"
                        class="py-3 px-6 font-medium border-b-2 transition-colors focus:outline-none"
                        :style="activeTab === 'coco' ? 'border-opacity: 1' : 'border-opacity: 0'"
                    >
                        COCO Format
                    </button>
                    <button
                        @click="activeTab = 'yolo'"
                        :class="activeTab === 'yolo' ? 'border-blue-500 text-blue-500' : 'text-slate-400 hover:text-gray-200'"
                        class="py-3 px-6 font-medium border-b-2 transition-colors focus:outline-none"
                        :style="activeTab === 'yolo' ? 'border-opacity: 1' : 'border-opacity: 0'"
                    >
                        YOLO Format
                    </button>
                    <button
                        @click="activeTab = 'labelme'"
                        :class="activeTab === 'labelme' ? 'border-blue-500 text-blue-500' : 'text-slate-400 hover:text-gray-200'"
                        class="py-3 px-6 font-medium border-b-2 transition-colors focus:outline-none"
                        :style="activeTab === 'labelme' ? 'border-opacity: 1' : 'border-opacity: 0'"
                    >
                        Labelme Format
                    </button>
                </div>

                {{-- COCO --}}
                <x-zip-format-info.coco/>

                {{-- YOLO --}}
                <x-zip-format-info.yolo/>

                {{-- Labelme --}}
                <x-zip-format-info.labelme/>
            </div>
        </div>
    </div>
</x-app-layout>
