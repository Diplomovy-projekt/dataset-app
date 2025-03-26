<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\Configs\TableDefinition;
use App\Mail\UserInvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class AdminUsers extends Component
{
    use WithPagination, WithoutUrlPagination;

    private array $tableIds = ['user-overview', 'pending-invites', 'expired-invites'];
    public array $tables = [];

    public array $users = [];
    public string $userSearchTerm = '';

    #[Computed]
    public function paginatedUserOverview()
    {
        return User::withCount('datasets')
            ->orderBy($this->tables['user-overview']['sortColumn'], $this->tables['user-overview']['sortDirection'])
            ->paginate(AppConfig::PER_PAGE_OPTIONS['10'], pageName: 'users');
    }
    #[Computed]
    public function paginatedPendingInvites()
    {
        return Invitation::pending()
            ->orderBy($this->tables['pending-invites']['sortColumn'], $this->tables['pending-invites']['sortDirection'])
            ->paginate(AppConfig::PER_PAGE_OPTIONS['10'], pageName: 'invites');
    }
    #[Computed]
    public function paginatedExpiredInvites()
    {
        return Invitation::expired()->notUsed()
            ->orderBy($this->tables['expired-invites']['sortColumn'], $this->tables['expired-invites']['sortDirection'])
            ->paginate(AppConfig::PER_PAGE_OPTIONS['10'], pageName: 'invites');
    }

    public function mount()
    {
        $this->users = User::all()->select('email', 'id', 'role', 'name')->toArray();
        foreach ($this->tableIds as $tableId) {
            $this->tables[$tableId] = TableDefinition::get($tableId);
        }
    }

    public function sortBy($tableId, $column)
    {
        if ($this->tables[$tableId]['sortColumn'] === $column) {
            $this->tables[$tableId]['sortDirection'] = $this->tables[$tableId]['sortDirection'] === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new column and default to ascending
            $this->tables[$tableId]['sortColumn'] = $column;
            $this->tables[$tableId]['sortDirection'] = 'asc';
        }
        $this->resetPage();
    }

    public function updateRole($id, $role)
    {
        try {
            if ($id === auth()->id()) {
                throw new \Exception("You cannot change your own role.");
            }

            $user = User::findOrFail($id);

            if (!in_array($role, ['admin', 'user'])) {
                throw new \InvalidArgumentException('Invalid role selected.');
            }

            $user->update(['role' => $role]);

            unset($this->paginatedUsers);

            $this->dispatch('flash-msg', type: 'success', message: 'Role updated successfully!');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('flash-msg', type: 'error', message: $e->getMessage());
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: $e->getMessage());
        }
    }

    public function toggleActiveUser($id)
    {
        try {
            $user = User::find($id);
            $user->update(['is_active' => !$user->is_active]);
            $this->dispatch('flash-msg', type: 'success', message: 'Action completed successfully!');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Action failed');
        }
        unset($this->paginatedUsers);
    }

    public function searchUsers()
    {
        $this->users = User::where('name', 'like', "%$this->userSearchTerm%")
            ->orWhere('email', 'like', "%$this->userSearchTerm%")
            ->select('email', 'id', 'role', 'name')
            ->get()
            ->toArray();
    }
    public function deleteUser($deleteUserId, $inheritDatasetsUserId)
    {
        if (auth()->id() == $deleteUserId) {
            $this->dispatch('flash-msg', type: 'error', message: 'You cannot delete yourself!');
            return;
        }
        try {
            DB::beginTransaction();

            $user = User::find($deleteUserId);
            // Update the datasets owner to the current admin
            $user->datasets()->update(['user_id' => $inheritDatasetsUserId]);
            $user->delete();
            Invitation::where('email', $user->email)->delete();

            DB::commit();
            $this->dispatch('flash-msg', type: 'success', message: 'User deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to delete user. Please try again.');
        }
        unset($this->paginatedUsers);
    }


    public function resendInvitation($invitationId)
    {
        $invitation = Invitation::notUsed()
            ->where('id', $invitationId)
            ->first();

        if (!$invitation) {
            $this->dispatch('flash-msg', type: 'error', message: 'Invitation not found.');
            return;
        }

        $token = Str::random(64);
        $invitation->update(['token' => $token]);

        try {
            Mail::to($invitation->email)->send(new UserInvitationMail($invitation));
            $this->dispatch('flash-msg', type: 'success', message: 'Invitation resent successfully!');
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to send invitation email.');
        }
        unset($this->pendingInvites);
    }

    public function cancelInvitation($invitationId)
    {
        $invitation = Invitation::notUsed()
            ->where('id', $invitationId)
            ->first();

        if (!$invitation) {
            $this->dispatch('flash-msg', type: 'error', message: 'Invitation is either already used or not found.');
            return;
        }

        if ($invitation->delete()) {
            $this->dispatch('flash-msg', type: 'success', message: 'Invitation cancelled successfully!');
        } else {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to cancel invitation. Please try again.');
        }
        unset($this->pendingInvites);
    }
}
