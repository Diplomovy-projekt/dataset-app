<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="bg-slate-800 p-6 rounded-lg border border-slate-700">
    <!-- Header -->
    <x-misc.header-with-line title="Update Password" info="Ensure your account is using a long, random password to stay secure."/>

    <!-- Form -->
    <form wire:submit="updatePassword" class="space-y-4">
        <!-- Current Password -->
        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-slate-400 mb-1">{{ __('Current Password') }}</label>
            <input
                wire:model="current_password"
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="w-full p-2 bg-slate-700 border border-slate-600 text-gray-200 rounded-md focus:outline-none focus:border-blue-500"
                autocomplete="current-password"
            >
            <x-input-error :messages="$errors->get('current_password')" class="mt-1 text-sm text-red-500" />
        </div>

        <!-- New Password -->
        <div>
            <label for="update_password_password" class="block text-sm font-medium text-slate-400 mb-1">{{ __('New Password') }}</label>
            <input
                wire:model="password"
                id="update_password_password"
                name="password"
                type="password"
                class="w-full p-2 bg-slate-700 border border-slate-600 text-gray-200 rounded-md focus:outline-none focus:border-blue-500"
                autocomplete="new-password"
            >
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-500" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-400 mb-1">{{ __('Confirm Password') }}</label>
            <input
                wire:model="password_confirmation"
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="w-full p-2 bg-slate-700 border border-slate-600 text-gray-200 rounded-md focus:outline-none focus:border-blue-500"
                autocomplete="new-password"
            >
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-sm text-red-500" />
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <x-primary-button>
                {{ __('Save') }}
            </x-primary-button>
            <x-action-message class="text-sm text-green-500 ml-3" on="password-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
