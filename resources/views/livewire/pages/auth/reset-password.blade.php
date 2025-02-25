<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.clean')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed'],
            //'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirect('login');
        //$this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md mx-auto p-6">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-200">Reset Your Password</h1>
                <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent ml-6"></div>
            </div>

            <!-- Form -->
            <form wire:submit.prevent="resetPassword">
                <!-- Email Field -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-slate-400 mb-2">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        class="p-1 w-full bg-slate-800 border border-slate-600 text-gray-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:outline-none"
                        placeholder="Enter your email"
                        required
                    >
                    @error('email') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                </div>

                <!-- Password Field -->
                <div class="mb-6" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-slate-400 mb-2">New Password</label>
                    <div class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            id="password"
                            wire:model="password"
                            class="p-1 w-full bg-slate-800 border border-slate-600 text-gray-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:outline-none"
                            placeholder="Create a new password"
                            required
                        >
                        <button
                            @click="show = !show"
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-gray-200"
                        >
                            <x-icon name="heroicon-o-eye" x-show="!show" class="w-5 h-5" />
                            <x-icon name="heroicon-o-eye-slash" x-show="show" class="w-5 h-5" />
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
                            placeholder="Repeat your new password"
                            required
                        >
                        <button
                            @click="show = !show"
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-gray-200"
                        >
                            <x-icon name="heroicon-o-eye" x-show="!show" class="w-5 h-5" />
                            <x-icon name="heroicon-o-eye-slash" x-show="show" class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="p-1 w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                >
                    <x-icon name="heroicon-o-lock-closed" class="w-5 h-5" />
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
