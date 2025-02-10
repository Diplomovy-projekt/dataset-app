@php
    // Dummy data for visualization
    $this->paginatedUsers = collect([
        [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'role' => 'admin',
            'status' => 'active',
            'last_active' => '5',
            'two_factor_enabled' => true
        ],
        [
            'id' => 2,
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'role' => 'manager',
            'status' => 'active',
            'last_active' => '4',
            'two_factor_enabled' => true
        ],
        [
            'id' => 3,
            'name' => 'Bob Wilson',
            'email' => 'bob.wilson@example.com',
            'role' => 'user',
            'status' => 'pending',
            'last_active' => '6',
            'two_factor_enabled' => false
        ],
        [
            'id' => 4,
            'name' => 'Alice Johnson',
            'email' => 'alice.johnson@example.com',
            'role' => 'user',
            'status' => 'active',
            'last_active' => '2',
            'two_factor_enabled' => true
        ],
        [
            'id' => 5,
            'name' => 'Charlie Brown',
            'email' => 'charlie.brown@example.com',
            'role' => 'manager',
            'status' => 'inactive',
            'last_active' => '4',
            'two_factor_enabled' => false
        ],
        [
            'id' => 6,
            'name' => 'Eva Davis',
            'email' => 'eva.davis@example.com',
            'role' => 'user',
            'status' => 'active',
            'last_active' => '1',
            'two_factor_enabled' => true
        ],
        [
            'id' => 7,
            'name' => 'Frank Miller',
            'email' => 'frank.miller@example.com',
            'role' => 'user',
            'status' => 'pending',
            'last_active' => '15',
            'two_factor_enabled' => false
        ]
    ]);

    // Pagination setup
    $this->paginatedUsers = new \Illuminate\Pagination\LengthAwarePaginator(
        $this->paginatedUsers,
        $this->paginatedUsers->count(),
        10,
        1
    );
@endphp

<div x-data="userManagement(@this)" class="p-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-200">User Management</h1>
        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent mx-6"></div>
    </div>

    <!-- Table Section -->
    <div class="bg-slate-800 rounded-xl overflow-hidden">
        <!-- Table Header -->
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

        <!-- Table Content -->
        <div class="w-full overflow-x-auto">
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
                                            <svg class="w-4 h-4 -mb-1"
                                                 :class="{ 'text-blue-400': sortField === '{{ $header['field'] }}' && sortDirection === 'asc' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                            <svg class="w-4 h-4"
                                                 :class="{ 'text-blue-400': sortField === '{{ $header['field'] }}' && sortDirection === 'desc' }"
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
                                      'bg-green-500/10 text-green-400': '{{ $user['status'] }}' === 'active',
                                      'bg-yellow-500/10 text-yellow-400': '{{ $user['status'] }}' === 'pending',
                                      'bg-red-500/10 text-red-400': '{{ $user['status'] }}' === 'inactive'
                                  }">
                                {{ $user['status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-gray-200">{{ $user['last_active'] }}</span>
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
                                    wire:confirm="Are you sure you want to deactivate this user?"
                                    danger
                                    :icon="@svg('mdi-account-off-outline')->toHtml()">
                                    Deactivate User
                                </x-dropdown-menu-item>
                            </x-dropdown-menu>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
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
        open: '',
        init() {
            // Initialize any required functionality
        },
    }));
</script>
@endscript
