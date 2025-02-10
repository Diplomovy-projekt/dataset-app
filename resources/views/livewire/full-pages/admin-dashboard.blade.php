@php
    // Dummy data for visualization
    $stats = [
        'active_users' => 147,
        'total_datasets' => 56,
        'storage_used' => '234.5 GB',
        'storage_limit' => '500 GB'
    ];

    $emailSettings = [
        'smtp_server' => 'smtp.mailtrap.io',
        'smtp_port' => '2525',
        'from_address' => 'noreply@example.com',
        'encryption' => 'tls'
    ];

    $storageSettings = [
        'provider' => 'AWS S3',
        'default_region' => 'eu-central-1',
        'bucket_name' => 'my-app-storage',
        'max_file_size' => '100 MB'
    ];
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
            <p class="text-3xl font-bold text-gray-200">{{ $stats['active_users'] }}</p>
        </div>

        <!-- Total Datasets Card -->
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-purple-500/10 p-2 rounded-lg">
                    <x-icon name="o-squares-2x2" class="w-5 h-5 text-purple-400" />
                </div>
                <h3 class="text-gray-200 font-semibold">Total Datasets</h3>
            </div>
            <p class="text-3xl font-bold text-gray-200">{{ $stats['total_datasets'] }}</p>
        </div>

        <!-- Storage Usage Card -->
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-green-500/10 p-2 rounded-lg">
                    <x-icon name="o-server" class="w-5 h-5 text-green-400" />
                </div>
                <h3 class="text-gray-200 font-semibold">Storage Usage</h3>
            </div>
            <p class="text-3xl font-bold text-gray-200">{{ $stats['storage_used'] }}</p>
            <p class="text-sm text-gray-400">of {{ $stats['storage_limit'] }}</p>
            <div class="w-full bg-slate-700 rounded-full h-2 mt-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: {{ (intval($stats['storage_used']) / intval($stats['storage_limit'])) * 100 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Settings Sections -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Email Settings -->
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
        </div>

        <!-- Storage Settings -->
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 border-b border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="bg-green-500/10 p-2 rounded-lg">
                        <x-icon name="o-server" class="w-5 h-5 text-green-400" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-200">Storage Settings</h2>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-2">
                    <label class="text-sm text-gray-400">Storage Provider</label>
                    <select class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-gray-200">
                        <option value="s3" {{ $storageSettings['provider'] == 'AWS S3' ? 'selected' : '' }}>AWS S3</option>
                        <option value="local">Local Storage</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm text-gray-400">Region</label>
                    <input type="text" value="{{ $storageSettings['default_region'] }}"
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-gray-200">
                </div>
                <div class="space-y-2">
                    <label class="text-sm text-gray-400">Bucket Name</label>
                    <input type="text" value="{{ $storageSettings['bucket_name'] }}"
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-gray-200">
                </div>
                <button class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    Save Storage Settings
                </button>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('adminDashboard', (wire) => ({
        init() {
            // Initialize any required functionality
        },
    }));
</script>
@endscript
