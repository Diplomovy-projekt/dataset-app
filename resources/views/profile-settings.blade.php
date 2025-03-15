<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Update Profile Information Section -->
            {{--<div class="p-6 sm:p-8    rounded-xl">
                <div class="max-w-2xl mx-auto">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>--}}

            <!-- Update Password Section -->
            <div class="p-6 sm:p-8    rounded-xl">
                <div class="max-w-2xl mx-auto">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="p-6 sm:p-8    rounded-xl">
                <div class="max-w-2xl mx-auto">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
