<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        //$this->redirectIntended(default: route('profile', absolute: false), navigate: true);
        $this->redirectIntended('profile');
    }
};
?>

<div class="w-full max-w-md mx-auto p-6">
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl border border-slate-700 p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-200">Log in</h1>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent ml-6"></div>
        </div>

        <!-- Form -->
        <form wire:submit="login">
            <!-- Email Field -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-slate-400 mb-2">Email</label>
                <input
                    type="email"
                    id="email"
                    wire:model="form.email"
                    class="p-1 w-full bg-slate-800 border border-slate-600 text-gray-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:outline-none"
                    placeholder="Enter your email"
                >
                @error('form.email')
                <span class="text-sm text-red-600 mt-2">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="mb-6" x-data="{ show: false }">
                <label for="password" class="block text-sm font-medium text-slate-400 mb-2">Password</label>
                <div class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        id="password"
                        wire:model="form.password"
                        class="p-1 w-full bg-slate-800 border border-slate-600 text-gray-200 rounded-lg px-4 py-2.5 focus:border-blue-500 focus:outline-none"
                        placeholder="Enter your password"
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
                @error('form.password')
                <span class="text-sm text-red-600 mt-2">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember" class="inline-flex items-center">
                    <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            {{--Forgot password--}}
            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-500 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                        Forgot your password?
                    </a>
                @endif

                <button
                    type="submit"
                    class="p-1 w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2"
                >
                    <i class="fas fa-sign-in-alt"></i>
                    Log in
                </button>
            </div>
        </form>
    </div>
</div>
