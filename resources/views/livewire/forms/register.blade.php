<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto p-6">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-200">Complete Registration</h1>
                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent ml-6"></div>
            </div>

            <!-- Form -->
            <form wire:submit.prevent="register">
                <!-- Name Field -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-slate-400 mb-2">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="p-1 w-full bg-slate-800 border border-slate-600 text-gray-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:outline-none"
                        placeholder="Enter your name"
                    >
                </div>

                <!-- Password Field -->
                <div class="mb-6" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-slate-400 mb-2">Password</label>
                    <div class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            id="password"
                            wire:model="password"
                            class="p-1 w-full bg-slate-800 border border-slate-600 text-gray-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:outline-none"
                            placeholder="Create a strong password"
                        >
                        <button
                            @click="show = !show"
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-gray-200"
                        >
                            <x-icon name="o-eye" x-show="!show" class="w-5 h-5" />
                            <x-icon name="o-eye-slash" x-show="show" class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Confirm Password Field -->
                <div class="mb-8" x-data="{ show: false }">
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-400 mb-2">Confirm Password</label>
                    <div class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            id="password_confirmation"
                            wire:model="password_confirmation"
                            class="p-1 w-full bg-slate-800 border border-slate-600 text-gray-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:outline-none"
                            placeholder="Repeat your password"
                        >
                        <button
                            @click="show = !show"
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-gray-200"
                        >
                            <x-icon name="o-eye" x-show="!show" class="w-5 h-5" />
                            <x-icon name="o-eye-slash" x-show="show" class="w-5 h-5" />


                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="p-1 w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                >
                    <x-icon name="o-user-plus" class="w-5 h-5" />
                    Complete Registration
                </button>
            </form>
        </div>
    </div>
</div>
