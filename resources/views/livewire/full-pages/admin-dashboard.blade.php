@php
    $dummyLogs = collect([
        (object)[
            'created_at' => now()->subHours(2),
            'user' => (object)[
                'name' => 'John Smith',
                'email' => 'john.smith@example.com'
            ],
            'description' => 'Uploaded new dataset "Sales Data 2024"',
            'type' => 'dataset'
        ],
        (object)[
            'created_at' => now()->subHours(3),
            'user' => (object)[
                'name' => 'Sarah Johnson',
                'email' => 'sarah.j@example.com'
            ],
            'description' => 'Modified user permissions',
            'type' => 'user'
        ],
        (object)[
            'created_at' => now()->subHours(5),
            'user' => (object)[
                'name' => 'Mike Anderson',
                'email' => 'mike.a@example.com'
            ],
            'description' => 'Downloaded dataset "Customer Survey Results"',
            'type' => 'dataset'
        ],
        (object)[
            'created_at' => now()->subHours(6),
            'user' => (object)[
                'name' => 'Emma Wilson',
                'email' => 'emma.w@example.com'
            ],
            'description' => 'System backup completed',
            'type' => 'system'
        ],
        (object)[
            'created_at' => now()->subHours(8),
            'user' => (object)[
                'name' => 'Alex Turner',
                'email' => 'alex.t@example.com'
            ],
            'description' => 'Updated dataset metadata',
            'type' => 'dataset'
        ],
        (object)[
            'created_at' => now()->subHours(10),
            'user' => (object)[
                'name' => 'Lisa Brown',
                'email' => 'lisa.b@example.com'
            ],
            'description' => 'Created new user account',
            'type' => 'user'
        ],
        (object)[
            'created_at' => now()->subHours(12),
            'user' => (object)[
                'name' => 'David Chen',
                'email' => 'david.c@example.com'
            ],
            'description' => 'Scheduled system maintenance',
            'type' => 'system'
        ],
        (object)[
            'created_at' => now()->subHours(14),
            'user' => (object)[
                'name' => 'Rachel Adams',
                'email' => 'rachel.a@example.com'
            ],
            'description' => 'Deleted outdated dataset',
            'type' => 'dataset'
        ]
    ]);

    // Convert to paginator
    $activityLogs = new \Illuminate\Pagination\LengthAwarePaginator(
        $dummyLogs,
        $dummyLogs->count(),
        10,
        1
    );
@endphp
<div x-data="adminDashboard(@this)" class="p-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-200">Admin Dashboard</h1>
        <div class="h-px flex-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent mx-6"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Active Users Card -->
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-blue-500/10 p-2 rounded-lg">
                    <x-icon name="o-users" class="w-5 h-5 text-blue-400" />
                </div>
                <h3 class="text-gray-200 font-semibold">Active Users</h3>
            </div>
            <p class="text-3xl font-bold text-gray-200">{{ $this->userCount}}</p>
        </div>

        <!-- Total Datasets Card -->
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-purple-500/10 p-2 rounded-lg">
                    <x-icon name="o-squares-2x2" class="w-5 h-5 text-purple-400" />
                </div>
                <h3 class="text-gray-200 font-semibold">Total Datasets</h3>
            </div>
            <p class="text-3xl font-bold text-gray-200">{{ $this->datasetCount }}</p>
        </div>

        <!-- Storage Usage Card -->
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-green-500/10 p-2 rounded-lg">
                    <x-icon name="o-server" class="w-5 h-5 text-green-400" />
                </div>
                <h3 class="text-gray-200 font-semibold">Dataset Storage</h3>
            </div>
            <p class="text-3xl font-bold text-gray-200">{{ $totalStorage }}GB</p>
            <p class="text-sm text-gray-400">Total space used by datasets</p>
        </div>
    </div>

    {{--<!-- Email Settings Section -->
    <div class="bg-slate-800 rounded-xl overflow-hidden">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="bg-blue-500/10 p-2 rounded-lg">
                    <x-icon name="o-envelope" class="w-5 h-5 text-blue-400" />
                </div>
                <h2 class="text-xl font-bold text-gray-200">Email Settings</h2>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="space-y-2">
                <label class="text-sm text-gray-400">SMTP Server</label>
                <input type="text" value="{{ $emailSettings['smtp_server'] }}"
                       class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-gray-200">
            </div>
            <div class="space-y-2">
                <label class="text-sm text-gray-400">SMTP Port</label>
                <input type="text" value="{{ $emailSettings['smtp_port'] }}"
                       class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-gray-200">
            </div>
            <div class="space-y-2">
                <label class="text-sm text-gray-400">From Address</label>
                <input type="email" value="{{ $emailSettings['from_address'] }}"
                       class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-gray-200">
            </div>
            <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                Save Email Settings
            </button>
        </div>
    </div>--}}
    <!-- Activity Logs Table -->
    <div class="bg-slate-800 rounded-xl overflow-hidden">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="bg-blue-500/10 p-2 rounded-lg">
                    <x-icon name="o-clock" class="w-5 h-5 text-blue-400" />
                </div>
                <h2 class="text-xl font-bold text-gray-200">Activity Logs</h2>
            </div>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="text-left border-b border-slate-700">
                        <th class="pb-3 px-4 text-sm font-semibold text-gray-400">Timestamp</th>
                        <th class="pb-3 px-4 text-sm font-semibold text-gray-400">User</th>
                        <th class="pb-3 px-4 text-sm font-semibold text-gray-400">Email</th>
                        <th class="pb-3 px-4 text-sm font-semibold text-gray-400">Action</th>
                        <th class="pb-3 px-4 text-sm font-semibold text-gray-400">Type</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                    @foreach($activityLogs as $log)
                        <tr
                            class="hover:bg-slate-700/50 transition-colors cursor-pointer"
                            @click="openLogDetail($el, {{ json_encode($log) }})"
                        >
                            <td class="py-3 px-4 text-sm text-gray-400">
                                {{ $log->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-200">
                                {{ $log->user->name }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-400">
                                {{ $log->user->email }}
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-200">
                                {{ Str::limit($log->description, 60) }}
                            </td>
                            <td class="py-3 px-4">
                                <span @class([
                                    'px-2 py-1 text-xs rounded-full',
                                    'bg-blue-500/10 text-blue-400' => $log->type === 'dataset',
                                    'bg-purple-500/10 text-purple-400' => $log->type === 'user',
                                    'bg-emerald-500/10 text-emerald-400' => $log->type === 'system',
                                ])>
                                    {{ $log->type }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/75"></div>

        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="relative bg-slate-800 rounded-xl max-w-2xl w-full overflow-hidden"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
            >
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 border-b border-slate-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-200">Activity Details</h2>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-200">
                            <!-- Close button (X) -->
                            <span class="text-lg">&times;</span>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div class="space-y-1">
                        <h3 class="text-sm font-medium text-gray-400">Timestamp</h3>
                        <p class="text-gray-200" x-text="formatDate(selectedLog?.created_at)"></p>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-medium text-gray-400">User</h3>
                        <p class="text-gray-200">
                            <span x-text="selectedLog?.user.name"></span>
                            (<span x-text="selectedLog?.user.email"></span>)
                        </p>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-medium text-gray-400">Description</h3>
                        <p class="text-gray-200" x-text="selectedLog?.description"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('adminDashboard', (wire) => ({
        showModal: false,
        selectedLog: null,

        openLogDetail(el, log) {
            this.selectedLog = log;
            this.showModal = true;
        },

        formatDate(date) {
            return new Date(date).toLocaleString();
        }
    }));
</script>
@endscript
