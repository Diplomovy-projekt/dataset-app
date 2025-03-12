@props([
    'table' => []
])
<x-tables.wrapper :table="$table">
    @foreach($this->paginatedUserOverview as $user)
        <x-tables.table-row id="admin-user-management-{{ $user['id'] }}">
            {{-- Name --}}
            <x-tables.table-cell>
                <div class="flex items-center gap-3">
                    <div class="bg-blue-500/10 p-2 rounded-lg">
                        <x-icon name="o-user" class="w-5 h-5 text-blue-400" />
                    </div>
                    <span class="text-gray-200">{{ $user['name'] }}</span>
                </div>
            </x-tables.table-cell>
            {{-- Email --}}
            <x-tables.table-cell>
                <span class="text-gray-200">{{ $user['email'] }}</span>
            </x-tables.table-cell>
            {{-- Role --}}
            <x-tables.table-cell>
                <div x-data="{ role: '{{ $user['role'] }}', isCurrentUser: {{ $user['id'] === auth()->id() ? 'true' : 'false' }} }">
                    <select class="bg-slate-700 text-gray-200 rounded-lg px-2 py-1 disabled:opacity-50"
                            x-model="role"
                            :disabled="isCurrentUser"
                            @change="if (!isCurrentUser) $wire.updateRole({{ $user['id'] }}, role)">
                        @foreach(App\Configs\AppConfig::AUTH_ROLES as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </x-tables.table-cell>
            {{-- Status --}}
            <x-tables.table-cell>
                            <span class="px-2 py-1 text-xs rounded-full"
                                  :class="{
                                      'bg-green-500/10 text-green-400': '{{ $user['is_active'] }}' === '1',
                                      'bg-red-500/10 text-red-400': '{{ $user['is_active'] }}' === '0'
                                  }">
                                {{ $user['is_active'] == '1' ? 'Active' : 'Inactive' }}
                            </span>
            </x-tables.table-cell>
            {{-- Datasets --}}
            <x-tables.table-cell>
                <span class="text-gray-200">{{ $user['datasets_count'] }}</span>
            </x-tables.table-cell>
            <x-tables.table-cell>
                <x-dropdown-menu direction="left" class="w-50">
                    <x-dropdown-menu-item
                        wire:click="toggleActiveUser({{ $user['id'] }})"
                        wire:confirm="This action will {{ $user['is_active'] ? 'deactivate' : 'activate' }} user and keep datasets under their ownership. Can be changed later"
                        :danger="$user['is_active']"
                        :icon="($user['is_active'] ? svg('mdi-account-lock-outline') : svg('mdi-account-check-outline'))->toHtml()">
                        {{ $user['is_active'] ? 'Deactivate User' : 'Activate User' }}
                    </x-dropdown-menu-item>

                    <x-dropdown-menu-item
                        @click="open = 'change-owner'; userToDelete = '{{ $user['id'] }}'"
                        danger
                        :icon="@svg('mdi-account-remove-outline')->toHtml()">
                        Delete User
                    </x-dropdown-menu-item>
                </x-dropdown-menu>
            </x-tables.table-cell>
        </x-tables.table-row>
    @endforeach
</x-tables.wrapper>
