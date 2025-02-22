<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\Mail\UserInvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdminUsers extends Component
{
    public array $authRoles = AppConfig::AUTH_ROLES;
    public int $pendingInvitesCount;
    public int $expiredInvitesCount;

    use WithPagination;

    public array $headers = [
        ['label' => 'Name', 'field' => 'name', 'sortable' => true, 'width' => 'w-64'],
        ['label' => 'Email', 'field' => 'email', 'sortable' => true, 'width' => 'w-20'],
        ['label' => 'Role', 'field' => 'role', 'sortable' => true, 'width' => 'w-18'],
        ['label' => 'Status', 'field' => 'status', 'sortable' => false, 'width' => 'w-18'],
        ['label' => 'Datasets', 'field' => 'datasets_count', 'sortable' => true, 'width' => 'w-16'],
        ['label' => 'Actions', 'field' => 'action', 'sortable' => false, 'width' => 'w-16'],
    ];

    public $sortColumn = 'name';
    public $sortDirection = 'asc';
    public array $users = [];
    public string $userSearchTerm = '';

    #[Computed]
    public function paginatedUsers()
    {
        return User::withCount('datasets')
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(AppConfig::PER_PAGE);
    }
    #[Computed]
    public function pendingInvites()
    {
        $invitations = Invitation::pending()->get();
        $this->pendingInvitesCount = $invitations->count();
        return $invitations;
    }
    #[Computed]
    public function expiredInvites()
    {
        $expiredInvites = Invitation::expired()->notUsed()->get();
        $this->expiredInvitesCount = $expiredInvites->count();
        return $expiredInvites;
    }

    public function mount()
    {
        $this->users = User::all()->select('email', 'id', 'role', 'name')->toArray();
    }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
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
