<div>
    <x-modals.fixed-modal modalId="invite-user" class="w-fit">
        <div x-data="{ email: '' }" class="p-4 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center gap-3">
                <div class="bg-blue-500 p-2 rounded-lg">
                    <x-icon name="o-user-plus" class="w-6 h-6 text-gray-200" />
                </div>
                <h1 class="text-2xl font-bold text-gray-200">Invite New User</h1>
            </div>
            <div class="h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>

            <!-- Form Section -->
            <div class="bg-slate-800 rounded-xl py-6">
                <div class="space-y-6">
                    <div class="flex flex-col gap-2">
                        <label for="email" class="text-gray-300 font-medium">User Email</label>
                        <input type="email" id="email" x-model="email" wire:model="email"
                               class="w-full bg-slate-700 text-gray-200 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400"
                               placeholder="Enter user email" required>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="role" class="text-gray-300 font-medium">User Role</label>
                        <select id="role" x-model="role" wire:model="role"
                                class="w-full bg-slate-700 text-gray-200 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(App\Configs\AppConfig::AUTH_ROLES as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex w-full">
                        <x-misc.button type="submit"
                                       full="true"
                                       variant="primary"
                                       wire:click="sendInvitation"
                                       :icon="@svg('o-paper-airplane')->toHtml()"
                        >
                        Send Invitation
                        </x-misc.button>
                    </div>
                </div>
            </div>
        </div>


    </x-modals.fixed-modal>
</div>
