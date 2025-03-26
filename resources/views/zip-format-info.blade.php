<x-app-layout>
    <div class="min-h-screen text-gray-200 p-6">
        <div class="max-w-6xl mx-auto">
            {{--Page Header--}}
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-6 rounded-xl border border-slate-700 mb-8">
                <h1 class="text-3xl font-bold mb-2">Dataset Upload Guidelines</h1>
                <p class="text-slate-400">Learn how to structure your ZIP files for different annotation formats</p>
            </div>

            {{--Format Selection Tabs--}}
            <div class="mb-8" x-data="{ activeTab: '{{ array_key_first(\App\Configs\AppConfig::ANNOTATION_FORMATS_INFO) }}' }">
                <div class="flex flex-wrap border-b border-slate-700 mb-6">
                    @foreach(\App\Configs\AppConfig::ANNOTATION_FORMATS_INFO as $format => $info)
                        <button
                            @click="activeTab = '{{ $format }}'"
                            :class="activeTab === '{{ $format }}' ? 'border-blue-500 text-blue-500' : 'text-slate-400 hover:text-gray-200'"
                            class="py-3 px-6 font-medium border-b-2 transition-colors focus:outline-none"
                            :style="activeTab === '{{ $format }}' ? 'border-opacity: 1' : 'border-opacity: 0'"
                        >
                            {{ $info['name'] }} Format
                        </button>
                    @endforeach
                </div>

                {{--Dynamically include format components--}}
                @foreach(\App\Configs\AppConfig::ANNOTATION_FORMATS_INFO as $format => $info)
                    <div x-show="activeTab === '{{ $format }}'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @if(view()->exists('components.zip-format-info.' . $format))
                            <x-dynamic-component :component="'zip-format-info.' . $format" />
                        @else
                            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="bg-amber-500 p-2 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-bold">{{ $info['name'] }} Format Structure</h2>
                                </div>
                                <p class="text-slate-300">Documentation for this format is coming soon. Please check back later.</p>
                                <div class="bg-slate-700 p-4 rounded-lg mt-6">
                                    <p class="text-amber-400 font-medium mb-2">Format Quick Info:</p>
                                    <ul class="list-disc list-inside text-slate-300 space-y-1">
                                        <li>File Extension: <span class="font-mono text-blue-400">.{{ $info['extension'] }}</span></li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
