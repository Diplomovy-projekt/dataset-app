<div x-data="userManagement(@this)" class="p-6">
    <!-- Header Section -->
    <x-misc.header-with-line title="User Management"/>

    <!-- Main Content -->
    <div class="bg-slate-800 rounded-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 border-b border-slate-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-500 p-2 rounded-lg">
                        <x-icon name="o-users" class="w-5 h-5 text-gray-200" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-200">Users</h2>
                </div>
                <livewire:forms.invite-user :modalId="'invite-user'"/>
                <button @click.prevent="open = 'invite-user'"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <div class="flex items-center gap-2">
                        <x-icon name="o-plus" class="w-4 h-4" />
                        <span>Invite User</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-slate-700">
            <div class="flex gap-6 px-6">
                <button
                    @click="activeTab = 'users'"
                    :class="{
                        'border-b-2 border-blue-500 text-blue-500': activeTab === 'users',
                        'text-gray-400 hover:text-gray-200': activeTab !== 'users'
                    }"
                    class="py-4 font-medium transition-colors">
                    Users
                </button>
                <button
                    @click="activeTab = 'pending'"
                    :class="{
                        'border-b-2 border-blue-500 text-blue-500': activeTab === 'pending',
                        'text-gray-400 hover:text-gray-200': activeTab !== 'pending'
                    }"
                    class="py-4 font-medium transition-colors flex items-center gap-2">
                    Pending Invitations
                    @if(count($this->pendingInvites) > 0)
                        <span class="px-2 py-0.5 text-xs bg-yellow-500/10 text-yellow-400 rounded-full">
                            {{count($this->pendingInvites)}}
                        </span>
                    @endif
                </button>
                <button
                    @click="activeTab = 'expired'"
                    :class="{
                        'border-b-2 border-blue-500 text-blue-500': activeTab === 'expired',
                        'text-gray-400 hover:text-gray-200': activeTab !== 'expired'
                    }"
                    class="py-4 font-medium transition-colors flex items-center gap-2">
                    Expired Invitations
                    @if(count($this->expiredInvites) > 0)
                        <span class="px-2 py-0.5 text-xs bg-red-500/10 text-red-400 rounded-full">
                            {{count($this->expiredInvites) }}
                        </span>
                    @endif
                </button>
            </div>
        </div>

        <!-- Active Users Table -->
        <div x-show="activeTab === 'users'" class="w-full overflow-x-auto">
            <table class="table-auto w-full border-collapse">
                <thead x-data="{ sortField: $wire.entangle('sortColumn'), sortDirection: $wire.entangle('sortDirection')}">
                <tr>
                    @foreach($headers as $header)
                        <th wire:key="admin-user-management-header-{{ $header['field'] }}"
                            class="px-6 py-3 text-left text-sm font-semibold text-gray-200 {{ $header['width'] }}">
                            @if($header['sortable'])
                                <button class="flex items-center gap-2 hover:text-blue-400 transition-colors"
                                        wire:click="sortBy('{{ $header['field'] }}')">
                                    {{ $header['label'] }}
                                    <span class="flex flex-col">
                                            <svg class="w-4 h-4 -mb-1" :class="{ 'text-blue-400': sortField === '{{ $header['field'] }}' && sortDirection === 'asc' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                            <svg class="w-4 h-4" :class="{ 'text-blue-400': sortField === '{{ $header['field'] }}' && sortDirection === 'desc' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </span>
                                </button>
                            @else
                                {{ $header['label'] }}
                            @endif
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                @foreach($this->paginatedUsers as $user)
                    <tr wire:key="admin-user-management-{{ $user['id'] }}"
                        class="hover:bg-slate-750 transition-colors">
                        {{-- Name --}}
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-500/10 p-2 rounded-lg">
                                    <x-icon name="o-user" class="w-5 h-5 text-blue-400" />
                                </div>
                                <span class="text-gray-200">{{ $user['name'] }}</span>
                            </div>
                        </td>
                        {{-- Email --}}
                        <td class="px-6 py-3">
                            <span class="text-gray-200">{{ $user['email'] }}</span>
                        </td>
                        {{-- Role --}}
                        <td class="px-6 py-3">
                            <div x-data="{ role: '{{ $user['role'] }}', isCurrentUser: {{ $user['id'] === auth()->id() ? 'true' : 'false' }} }">
                                <select class="bg-slate-700 text-gray-200 rounded-lg px-2 py-1 disabled:opacity-50"
                                        x-model="role"
                                        :disabled="isCurrentUser"
                                        @change="if (!isCurrentUser) $wire.updateRole({{ $user['id'] }}, role)">
                                    @foreach($authRoles as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                        {{-- Status --}}
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs rounded-full"
                                  :class="{
                                      'bg-green-500/10 text-green-400': '{{ $user['is_active'] }}' === '1',
                                      'bg-red-500/10 text-red-400': '{{ $user['is_active'] }}' === '0'
                                  }">
                                {{ $user['is_active'] == '1' ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        {{-- Datasets --}}
                        <td class="px-6 py-3">
                            <span class="text-gray-200">{{ $user['datasets_count'] }}</span>
                        </td>
                        <td class="px-6 py-3">
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
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pending Invitations -->
        <div x-show="activeTab === 'pending'" class="divide-y divide-slate-700">
            @foreach($this->pendingInvites as $invite)
                <div wire:key="admin-user-management-pending-{{ $invite['id'] }}"
                     class="p-4 hover:bg-slate-750 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="bg-yellow-500/10 p-2 rounded-lg">
                                <x-icon name="o-envelope" class="w-5 h-5 text-yellow-400" />
                            </div>
                            <div>
                                <div class="text-gray-200">{{ $invite['email'] }}</div>
                                <div class="text-sm text-gray-400">Invited {{ \Carbon\Carbon::parse($invite['updated_at'])->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-misc.button
                                wire:click="resendInvitation({{ $invite['id'] }})"
                                color="blue"
                                size="sm">
                                Resend Invitation
                            </x-misc.button>
                            <x-misc.button
                                wire:click="cancelInvitation({{ $invite['id'] }})"
                                color="red"
                                size="sm">
                                Cancel Invitation
                            </x-misc.button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


        <!-- Expired Invitations -->
        <div x-show="activeTab === 'expired'" class="divide-y divide-slate-700">
            @foreach($this->expiredInvites as $invite)
                <div wire:key="admin-user-management-expired-{{ $invite['id'] }}" class="p-4 hover:bg-slate-750 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="bg-red-500/10 p-2 rounded-lg">
                                <x-icon name="o-clock" class="w-5 h-5 text-red-500" />
                            </div>
                            <div>
                                <div class="text-gray-200">{{ $invite['email'] }}</div>
                                <div class="text-sm text-gray-400">Invited {{ \Carbon\Carbon::parse($invite['updated_at'])->diffForHumans() }}</div>
                            </div>
                        </div>

                        <x-misc.button
                            wire:click="resendInvitation({{ $invite['id'] }})"
                            color="blue"
                            size="sm">
                            Resend Invitation
                        </x-misc.button>
                    </div>
                </div>
            @endforeach
        </div>

        <x-modals.fixed-modal modalId="change-owner" class="w-fit">
            <div class="p-4">
                {{-- Header --}}
                <x-misc.header-with-line title="Change Dataset Ownership" info="Select a new owner before deleting the user. The dataset will be transferred automatically."/>

                {{-- Search input --}}
                <div class="relative mb-3">
                    <input type="text"
                           wire:model.live="userSearchTerm"
                           wire:keyup.debounce.500ms="searchUsers"
                           placeholder="Search users..."
                           class="w-full px-3 py-2 bg-slate-700 rounded-md text-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                {{-- Users list --}}
                <div class="mb-3 p-3 pl-0 border-b border-slate-700 bg-slate-800 rounded-md">
                    {{-- Current User Option --}}
                    <label class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors cursor-pointer font-medium text-gray-300 bg-blue-700/10 border border-blue-500">
                        <input type="radio" x-model="inheritUser" value="{{ auth()->id() }}" class="form-radio text-blue-500">
                        <div class="bg-blue-600 p-1.5 rounded-full">
                            <x-icon name="o-user" class="w-4 h-4 text-white" />
                        </div>
                        <span class="text-white font-semibold text-sm">Current User (You)</span>
                    </label>
                </div>
                <div class="max-h-48 overflow-y-auto space-y-1">
                    @foreach($users as $user)
                        @if($user['id'] !== auth()->id())
                            <label class="flex items-center justify-between gap-3 px-3 py-2 hover:bg-slate-700 rounded-md transition-colors cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <input type="radio" x-model="inheritUser" value="{{ $user['id'] }}" class="form-radio text-blue-500">
                                    <div class="bg-slate-600 p-1.5 rounded-full">
                                        <x-icon name="o-user" class="w-4 h-4 text-gray-300" />
                                    </div>
                                    <div>
                                        <span class="text-white font-medium text-sm">{{ $user['name'] }}</span>
                                        <p class="text-gray-400 text-xs">{{ $user['email'] }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-md"
                                      :class="{ 'bg-red-600 text-white': {{ $user['role'] === 'admin' ? 'true' : 'false' }}, 'bg-gray-600 text-gray-200': {{ $user['role'] === 'user' ? 'true' : 'false' }} }">
                                    {{ ucfirst($user['role']) }}
                                </span>
                            </label>
                        @endif
                    @endforeach

                </div>

                {{-- Delete User Button (Transfers Ownership Before Deletion) --}}
                <div class="mt-3 text-right">
                    <x-misc.button @click="$wire.deleteUser(userToDelete, inheritUser)" color="red" size="sm" x-bind:disabled="!inheritUser">
                        Delete User & Transfer Ownership
                    </x-misc.button>
                </div>
            </div>
        </x-modals.fixed-modal>





        <!-- Pagination -->
    <div class="mt-4">
        {{ $this->paginatedUsers->links() }}
    </div>
</div>



@script
<script>
    Alpine.data('userManagement', (wire) => ({
        activeTab: 'users',
        inheritUser: '{{ auth()->id() }}',
        userToDelete: '',
        open: '',
        init() {
            // Initialize any required functionality
        },
    }));
</script>
@endscript
