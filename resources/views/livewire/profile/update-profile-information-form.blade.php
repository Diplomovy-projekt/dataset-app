<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="bg-slate-800 p-6 rounded-lg border border-slate-700">

    <x-misc.header title="Profile Information" info="Update your account's profile information and email address."/>


    {{--Form--}}
    <form wire:submit="updateProfileInformation" class="space-y-4">
        {{--Name--}}
        <div>
            <label for="name" class="block text-sm font-medium text-slate-400 mb-1">{{ __('Name') }}</label>
            <input
                wire:model="name"
                id="name"
                name="name"
                type="text"
                class="w-full p-2 bg-slate-700 border border-slate-600 text-gray-200 rounded-md focus:outline-none focus:border-blue-500"
                required
                autofocus
                autocomplete="name"
            >
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-sm text-red-500" />
        </div>

        {{--Email--}}
        <div>
            <label for="email" class="block text-sm font-medium text-slate-400 mb-1">{{ __('Email') }}</label>
            <input
                wire:model="email"
                id="email"
                name="email"
                type="email"
                class="w-full p-2 bg-slate-700 border border-slate-600 text-gray-200 rounded-md focus:outline-none focus:border-blue-500"
                required
                autocomplete="username"
            >
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-500" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-2 text-sm text-gray-400">
                    <p>{{ __('Your email address is unverified.') }}
                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{--Save Button--}}
        <div class="flex justify-end">
            <x-primary-button>
                {{ __('Save') }}
            </x-primary-button>
            <x-action-message class="text-sm text-green-500 ml-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>

