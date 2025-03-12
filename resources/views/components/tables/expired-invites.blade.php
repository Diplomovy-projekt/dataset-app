@props([
    'table' => []
])
<x-tables.wrapper :table="$table">
    @foreach($this->paginatedExpiredInvites as $invite)
        <x-tables.table-row id="admin-user-management-expired-{{ $invite['id'] }}">
            {{-- Invited email --}}
            <x-tables.table-cell>
                <div class="flex items-center gap-4">
                    <div class="bg-red-500/10 p-2 rounded-lg">
                        <x-icon name="o-clock" class="w-5 h-5 text-red-500" />
                    </div>
                    <div>
                        <div class="text-gray-200">{{ $invite['email'] }}</div>
                        <div class="text-sm text-gray-400">Invited {{ \Carbon\Carbon::parse($invite['updated_at'])->diffForHumans() }}</div>
                    </div>
                </div>
            </x-tables.table-cell>

            {{-- Role --}}
            <x-tables.table-cell>
                <span class="text-gray-200">{{ $invite['role'] }}</span>
            </x-tables.table-cell>

            {{-- Invite By --}}
            <x-tables.table-cell>
                <span class="text-gray-200">{{ $invite['invited_by'] }}</span>
            </x-tables.table-cell>


            {{-- Action buttons --}}
            <x-tables.table-cell>
                <div class="flex items-center gap-3">
                    <x-misc.button
                        wire:click="resendInvitation({{ $invite['id'] }})"
                        color="blue"
                        size="sm">
                        Resend Invitation
                    </x-misc.button>
                </div>
            </x-tables.table-cell>
        </x-tables.table-row>
    @endforeach
</x-tables.wrapper>
