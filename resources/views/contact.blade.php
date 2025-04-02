<x-app-layout>
    <div class="p-6">
        <div class="max-w-lg mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <x-misc.header title="Contact Us" align="center" font="text-3xl" info="We're here to help with any questions"></x-misc.header>
            </div>

            <!-- Contact Info Card -->
            <div class="rounded-xl p-6 border border-slate-700 text-center">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <div class="bg-blue-500 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Get in Touch</h2>
                </div>
                <p class="text-slate-400 mb-4">You can reach us at the email below:</p>

                <div x-data="{ copyText: 'admin@yoursite.com', copied: false }" class="bg-slate-800 p-4 rounded-lg border border-slate-600 inline-flex items-center gap-3 relative">
                    <span class="text-blue-400 font-medium">admin@hcportal.eu</span>
                    <button @click="navigator.clipboard.writeText(copyText); copied = true; setTimeout(() => copied = false, 2000)" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm">
                        Copy
                    </button>
                    <span x-cloak x-show="copied" x-transition class="absolute -top-6 right-0 bg-green-500 text-white text-xs px-2 py-1 rounded-lg">Copied!</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
