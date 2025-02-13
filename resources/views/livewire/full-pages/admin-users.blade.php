<div x-data="userManagement(@this)" class="p-6">
    <!-- Header Section -->
    <div x-data="{
    notifications: [],
    add(message) {
        this.notifications.push({
            id: Date.now(),
            type: message.type,
            message: message.message
        });
        setTimeout(() => this.remove(this.notifications[0].id), 3000);
    },
    remove(id) {
        this.notifications = this.notifications.filter(notification => notification.id !== id);
    }
}"
         @notify.window="add($event.detail)"
         class="fixed top-4 right-4 z-50">

        <template x-for="notification in notifications" :key="notification.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-8"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-8"
                 :class="{
                'bg-green-500': notification.type === 'success',
                'bg-red-500': notification.type === 'error'
                'bg-yellow-500': notification.type === 'warning'
             }"
                 class="mb-4 p-4 rounded-lg text-white shadow-lg">
                <div class="flex items-center gap-3">
                    <template x-if="notification.type === 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </template>
                    <template x-if="notification.type === 'error'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </template>
                    <span x-text="notification.message"></span>
                </div>
            </div>
        </template>
    </div>

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
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-200 {{ $header['width'] }}">
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
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-500/10 p-2 rounded-lg">
                                    <x-icon name="o-user" class="w-5 h-5 text-blue-400" />
                                </div>
                                <span class="text-gray-200">{{ $user['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-gray-200">{{ $user['email'] }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <div x-data="{ role: '{{ $user['role'] }}' }">
                                <select class="bg-slate-700 text-gray-200 rounded-lg px-2 py-1"
                                        x-model="role"
                                        @change="$wire.updateRole({{ $user['id'] }}, role)">
                                    @foreach($authRoles as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </td>

                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs rounded-full"
                                  :class="{
                                      'bg-green-500/10 text-green-400': '{{ $user['is_active'] }}' === 'true',
                                      'bg-red-500/10 text-red-400': '{{ $user['is_active'] }}' === 'false'
                                  }">
                                {{ $user['is_active'] === 'true' ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-gray-200">{{ $user['datasets_count'] }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <x-dropdown-menu direction="left" class="w-50">
                                <x-dropdown-menu-item
                                    @click.prevent="open = 'edit-user'"
                                    :icon="@svg('eos-edit')->toHtml()">
                                    Edit User
                                </x-dropdown-menu-item>

                                <x-dropdown-menu-item
                                    @click.prevent="open = 'reset-password'"
                                    :icon="@svg('eva-lock')->toHtml()">
                                    Reset Password
                                </x-dropdown-menu-item>

                                <div class="border-t border-gray-300"></div>

                                <x-dropdown-menu-item
                                    wire:click="deactivateUser({{ $user['id'] }})"
                                    wire:confirm="This action will deactivate user and keep datasets under their ownership. Can be reactivated later"
                                    danger
                                    :icon="@svg('mdi-account-lock-outline')->toHtml()">
                                    Deactivate User
                                </x-dropdown-menu-item>

                                <x-dropdown-menu-item
                                    wire:click="deleteUser({{ $user['id'] }})"
                                    wire:confirm="This action will permanently delete the user and transfer all his datasets to you."
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
                <div class="p-4 hover:bg-slate-750 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="bg-yellow-500/10 p-2 rounded-lg">
                                <x-icon name="o-envelope" class="w-5 h-5 text-yellow-400" />
                            </div>
                            <div>
                                <div class="text-gray-200">{{ $invite['email'] }}</div>
                                <div class="text-sm text-gray-400">Invited {{ $invite['last_active'] }} days ago</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button wire:click="resendInvitation({{ $invite['id'] }})"
                                    class="px-3 py-1.5 text-sm bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500/20 transition-colors">
                                Resend Invitation
                            </button>
                            <button wire:click="cancelInvitation({{ $invite['id'] }})"
                                    wire:confirm="Are you sure you want to cancel this invitation?"
                                    class="px-3 py-1.5 text-sm bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition-colors">
                                Cancel Invitation
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


        <!-- Expired Invitations -->
        <div x-show="activeTab === 'expired'" class="divide-y divide-slate-700">
            @foreach($this->expiredInvites as $invite)
                <div class="p-4 hover:bg-slate-750 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="bg-red-500/10 p-2 rounded-lg">
                                <x-icon name="o-clock" class="w-5 h-5 text-red-500" />
                            </div>
                            <div>
                                <div class="text-gray-200">{{ $invite['email'] }}</div>
                                <div class="text-sm text-gray-400">Invited {{ $invite['last_active'] }} days ago</div>
                            </div>
                        </div>
                        <button wire:click="resendInvitation({{ $invite['id'] }})"
                                class="px-3 py-1.5 text-sm bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500/20 transition-colors">
                            Resend Invitation
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <!-- Pagination -->
    <div class="mt-4">
        {{ $this->paginatedUsers->links() }}
    </div>
</div>



@script
<script>
    Alpine.data('userManagement', (wire) => ({
        activeTab: 'users',
        open: '',
        init() {
            // Initialize any required functionality
        },
    }));
</script>
@endscript
