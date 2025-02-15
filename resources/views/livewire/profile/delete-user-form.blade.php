<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deactivateUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), function ($user) use ($logout) {
            $logout();
            $user->update(['is_active' => false]);
        });

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="bg-slate-800 p-6 rounded-lg border border-slate-700">
    <!-- Header -->
    <x-misc.header-with-line title="Deactivate Account" info="Once your account is deactivated, all of its resources and data will be reassigned under admins ownership"/>

    <!-- Delete Button -->
    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="w-full sm:w-auto"
    >
        {{ __('Deactivate Account') }}
    </x-danger-button>

    <!-- Modal -->
    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deactivateUser" class="space-y-6 p-6 bg-slate-800 rounded-lg shadow-xl">
            <h2 class="text-lg font-medium text-white">{{ __('Are you sure you want to deactivate your account?') }}</h2>
            <p class="mt-2 text-sm text-gray-300">{{ __("Once your account is deactivated, all of its resources and data will be reassigned under admin's ownership. Please enter your password to confirm you would like to deactivate your account. Admin can reactivate this account later") }}</p>

            <!-- Password Field -->
            <div>
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full p-2 bg-slate-700 border border-slate-600 text-gray-200 rounded-md focus:outline-none focus:border-blue-500"
                    placeholder="{{ __('Password') }}"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500" />
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end gap-4">
                <x-secondary-button x-on:click="$dispatch('close')" class="w-auto">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Deactivate Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
