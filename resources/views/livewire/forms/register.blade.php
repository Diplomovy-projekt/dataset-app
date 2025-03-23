<div x-data="registerComponent(@this)"
     class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto p-6">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-8">
            {{--Header--}}
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-200">Complete Registration</h1>
                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent ml-6"></div>
            </div>

            {{--Form--}}
            <form wire:submit.prevent="register">
                {{--Name Field--}}
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

                {{--Password Field--}}
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

                {{--Confirm Password Field--}}
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

                {{--GDPR Compliance Agreement--}}
                <div class="mb-6">
                    <div class="inline-flex items-center text-sm text-gray-200 cursor-pointer">
                        <input type="checkbox" id="agree" class="mr-2" wire:model="agreed">
                        <span class="text-sm">I agree to the
                            <span @click.prevent="open = 'gdpr-compliance'" class="text-blue-500 cursor-pointer">Terms and Conditions</span>.
                        </span>
                    </div>
                </div>


                {{--Submit Button--}}
                <button
                    type="submit"
                    :disabled="!agreed"
                    class="p-1 w-full bg-blue-600 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                    :class="{
        'bg-gray-400/50 cursor-not-allowed': !agreed,
        'hover:bg-blue-500': agreed
    }"
                >
                    <x-icon name="o-user-plus" class="w-5 h-5" />
                    Complete Registration
                </button>
            </form>
        </div>
    </div>

    <x-modals.fixed-modal modalId="gdpr-compliance" class="w-3/4 max-w-2xl text-gray-300 bg-slate-800 p-6 rounded-lg shadow-lg">
        <h2 class="text-3xl font-semibold mb-6 text-center text-gray-200">Terms and Conditions</h2>
        <p class="mb-6 text-lg text-gray-400 text-center">By registering on this platform, you agree to the following terms:</p>
        <ul class="list-disc pl-6 space-y-4 text-gray-300">
            <li>By uploading data to the platform, you acknowledge and consent to the application managing the data as part of its operations.</li>
            <li>The application reserves the right to store, manage, and process datasets as necessary to ensure the functionality and operation of the platform, including sharing the data as required.</li>
            <li>You retain the right to access and view your uploaded datasets at any time. However, any modifications or deletions to the datasets are subject to administrative approval.</li>
            <li>By accepting these terms, you agree to adhere to all legal, regulatory, and operational requirements established by the platform.</li>
        </ul>
        <div class="flex justify-end mt-6">
            <button @click="open = false; $wire.set('agreed', true)" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                I Agree
            </button>
        </div>
    </x-modals.fixed-modal>

</div>

@script
<script>
    Alpine.data('registerComponent', (wire) => ({
        // Modal
        open: '',
        agreed: $wire.entangle('agreed'),
        init() {
        },
    }));
</script>
@endscript
