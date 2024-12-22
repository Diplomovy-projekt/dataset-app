<x-app-layout>
    <div class="relative w-full h-screen ">
        <div class="absolute inset-0 flex items-center justify-center text-gray-200">
            <div class="text-center max-w-4xl mx-6">
                <h1 class="text-6xl font-extrabold   leading-tight mb-8 tracking-wide">
                    Discover, Create, and Elevate Your Datasets
                </h1>

                <p class="text-2xl text-gray-300 mb-10 px-6">
                    Unlock the potential of your research with custom datasets. Whether you're exploring, building, or analyzing, our intuitive platform helps you create the exact data you need.
                </p>

                <div class="flex justify-center space-x-6 mb-12">
                    <a href="{{ route('builder') }}" class="px-8 py-4 bg-blue-500   font-semibold rounded-lg shadow-lg transform hover:scale-105 hover:bg-blue-600 transition-all duration-300 ease-in-out">
                        Create Your Own Dataset
                    </a>

                    <a href="{{ route('dataset.index') }}" class="px-8 py-4 bg-gray-700   font-semibold rounded-lg shadow-lg transform hover:scale-105 hover:bg-gray-600 transition-all duration-300 ease-in-out">
                        View Our Datasets
                    </a>
                </div>

                <div class="mt-10 text-gray-400">
                    <p class="text-xl mb-6">
                        <span class="font-bold text-blue-400">Why Choose Us?</span>
                    </p>
                    <div class="flex justify-center space-x-12">
                        <div class="max-w-xs text-center">
                            <div class="text-4xl mb-4 text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-12 h-12 mx-auto">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m9-9H3"></path>
                                </svg>
                            </div>
                            <p class="text-lg">Fully Customizable</p>
                        </div>
                        <div class="max-w-xs text-center">
                            <div class="text-4xl mb-4 text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-12 h-12 mx-auto">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <p class="text-lg">Easy to Use</p>
                        </div>
                        <div class="max-w-xs text-center">
                            <div class="text-4xl mb-4 text-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-12 h-12 mx-auto">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m9-9H3"></path>
                                </svg>
                            </div>
                            <p class="text-lg">Powerful Insights</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
