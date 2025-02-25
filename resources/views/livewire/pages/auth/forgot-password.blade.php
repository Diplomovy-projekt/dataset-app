<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.clean')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto p-6">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-200">Forgot Password</h1>
                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent ml-6"></div>
            </div>

            <!-- Form -->
            <form wire:submit.prevent="sendPasswordResetLink">
                <!-- Info Text -->
                <div class="mb-6 text-sm text-slate-400">
                    No worries, just enter your email address and we’ll send you a password reset link.
                </div>

                <!-- Email Address -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-slate-400 mb-2">Email</label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        class="p-1 w-full bg-slate-800 border border-slate-600 text-gray-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:outline-none"
                        placeholder="Enter your email"
                        required
                    >
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="p-1 w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                >
                    <x-icon name="heroicon-s-envelope" class="w-5 h-5" />
                    Send Password Reset Link
                </button>
            </form>
        </div>
    </div>
</div>
