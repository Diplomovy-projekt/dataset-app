<x-app-layout>
    <div class="
    rounded-2xl my-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-24 sm:pb-12">
            <!-- Hero Section -->
            <div class="text-center">
                <h1 class="text-4xl sm:text-6xl font-bold text-gray-200 leading-tight mb-8 tracking-wide bg-gradient-to-r from-gray-200 to-gray-100 bg-clip-text text-transparent">
                    Discover, Create, and Elevate Your Datasets
                </h1>

                <div class="max-w-4xl mx-auto rounded-xl p-8 backdrop-blur-sm mb-12">
                    <p class="text-xl sm:text-2xl text-gray-300 leading-relaxed">
                        Unlock the potential of your research with custom datasets. Whether you're exploring, building, or analyzing, our intuitive platform helps you create the exact data you need.
                    </p>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-4xl mx-auto mb-12">
                    <div class="bg-slate-800/40 rounded-xl p-6 border border-slate-700">
                        <div class="text-3xl font-bold text-blue-400 mb-2">{{ $statistics['total_images'] }}</div>
                        <div class="text-gray-400">Images</div>
                    </div>
                    <div class="bg-slate-800/40 rounded-xl p-6 border border-slate-700">
                        <div class="text-3xl font-bold text-blue-400 mb-2">{{ $statistics['total_annotations'] }}</div>
                        <div class="text-gray-400">Annotations</div>
                    </div>
                    <div class="bg-slate-800/40 rounded-xl p-6 border border-slate-700">
                        <div class="text-3xl font-bold text-blue-400 mb-2">{{ $statistics['total_classes'] }}</div>
                        <div class="text-gray-400">Classes</div>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-6 mb-16">
                    <a wire:navigate href="{{ route('builder') }}"
                       class="group relative px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-500 rounded-xl font-semibold text-gray-200 transition-all duration-300 hover:shadow-[0_0_20px_rgba(59,130,246,0.3)]">
                        <div class="flex items-center justify-center gap-3">
                            <span>Create Your Own Dataset</span>
                            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </div>
                    </a>

                    <a wire:navigate href="{{ route('dataset.index') }}"
                       class="px-8 py-4 bg-slate-700 rounded-xl font-semibold text-gray-200 border border-slate-600 hover:bg-slate-600 transition-all duration-300">
                        View Our Datasets
                    </a>
                </div>

                <!-- Features Section -->
                {{--<div class="max-w-5xl mx-auto">
                    <h2 class="text-2xl font-bold text-gray-200 mb-10">
                        <span class="bg-gradient-to-r from-blue-400 to-blue-500 bg-clip-text text-transparent">
                            Why Choose Us?
                        </span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                        <div class="bg-slate-800/40 rounded-xl p-6 border border-slate-700">
                            <div class="bg-blue-500/20 p-4 rounded-lg w-16 h-16 mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-8 h-8 text-blue-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m9-9H3"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-200 mb-2">Fully Customizable</h3>
                            <p class="text-gray-400">Tailor your datasets to match your specific requirements</p>
                        </div>

                        <div class="bg-slate-800/40 rounded-xl p-6 border border-slate-700">
                            <div class="bg-blue-500/20 p-4 rounded-lg w-16 h-16 mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-8 h-8 text-blue-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-200 mb-2">Easy to Use</h3>
                            <p class="text-gray-400">Intuitive interface designed for efficiency</p>
                        </div>

                        <div class="bg-slate-800/40 rounded-xl p-6 border border-slate-700">
                            <div class="bg-blue-500/20 p-4 rounded-lg w-16 h-16 mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-8 h-8 text-blue-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-200 mb-2">Powerful Insights</h3>
                            <p class="text-gray-400">Detailed analytics and visualization tools</p>
                        </div>
                    </div>
                </div>--}}
            </div>
        </div>
    </div>
</x-app-layout>
