<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdminUsers extends Component
{
    public $authRoles = AppConfig::AUTH_ROLES;

    use WithPagination;

    public array $headers = [
        ['label' => 'Name', 'field' => 'name', 'sortable' => true, 'width' => 'w-64'],
        ['label' => 'Email', 'field' => 'email', 'sortable' => true, 'width' => 'w-20'],
        ['label' => 'Role', 'field' => 'role', 'sortable' => true, 'width' => 'w-18'],
        ['label' => 'Status', 'field' => 'status', 'sortable' => false, 'width' => 'w-18'],
        ['label' => 'Datasets', 'field' => 'dataset_count', 'sortable' => true, 'width' => 'w-16'],
        ['label' => 'Actions', 'field' => 'action', 'sortable' => false, 'width' => 'w-16'],
    ];

    public $sortColumn = 'display_name';
    public $sortDirection = 'asc';

    #[Computed]
    public function paginatedUsers()
    {
        return User::withCount('datasets')
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(AppConfig::PER_PAGE);
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
        $user = User::find($id);
        $user->update(['role' => $role]);
    }
}
