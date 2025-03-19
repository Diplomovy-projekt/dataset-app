<x-app-layout>
    <div class="min-h-screen  py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <x-misc.header title="Contact us" align="center" font="text-3xl"></x-misc.header>
                <p class="text-slate-400 mt-2">We're here to help with any questions</p>
            </div>

            <!-- Contact Form Card -->
            <div class=" rounded-xl p-6 border border-slate-700">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-blue-500 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Send a Message</h2>
                </div>

                <form x-data="{ name: '', email: '', message: '', submitted: false }"
                      @submit.prevent="submitted = true">
                    <div class="space-y-5">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Your Name</label>
                            <input type="text" id="name" x-model="name"
                                   class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-white"
                                   placeholder="Enter your name">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
                            <input type="email" id="email" x-model="email"
                                   class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-white"
                                   placeholder="Enter your email">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-300 mb-1">Message</label>
                            <textarea id="message" x-model="message" rows="4"
                                      class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-white"
                                      placeholder="How can we help you?"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                            Send Message
                        </button>

                        <div x-show="submitted" x-transition
                             class="p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400 text-sm">
                            Thank you for your message! We'll get back to you soon.
                        </div>
                    </div>
                </form>

                <div class="mt-6 pt-5 border-t border-slate-700">
                    <p class="text-sm text-slate-400">
                        Prefer to email us directly? Reach us at
                        <a href="mailto:admin@yoursite.com" class="text-blue-400 hover:underline">admin@yoursite.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
