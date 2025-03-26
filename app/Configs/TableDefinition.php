<?php

namespace App\Configs;

class TableDefinition
{
    /**
     * Get a table definition by its ID
     *
     * @param string $id The table ID
     * @return array|null The table definition or null if not found
     */
    public static function get(string $id): ?array
    {
        return self::$tables[$id] ?? null;
    }

    /**
     * Get all table definitions
     *
     * @return array All table definitions
     */
    public static function getAll(): array
    {
        return self::$tables;
    }

    private static array $tables = [
        // ADMIN DATASETS MANAGEMENT
        'dataset-overview' => [
            'id' => 'dataset-overview',
            'headers' => [
                ['label' => 'Display Name', 'field' => 'display_name', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Categories', 'field' => 'categories', 'sortable' => false, 'width' => 'w-20'],
                ['label' => 'Annotation Technique', 'field' => 'annotation_technique', 'sortable' => true, 'width' => 'w-18'],
                ['label' => 'Owner', 'field' => 'user.email', 'sortable' => true, 'width' => 'w-18'],
                ['label' => 'Visibility', 'field' => 'is_public', 'sortable' => true, 'width' => 'w-16'],
                ['label' => 'Pending Changes', 'field' => 'pending_changes', 'sortable' => false, 'width' => 'w-14'],
                ['label' => 'Actions', 'field' => 'actions', 'sortable' => false, 'width' => 'w-14'],
            ],
            'sortColumn' => 'display_name',
            'sortDirection' => 'asc',
        ],
        'pending-requests' => [
            'id' => 'pending-requests',
            'headers' => [
                ['label' => 'Dataset', 'field' => 'dataset.display_name', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Requested By', 'field' => 'user.email', 'sortable' => true, 'width' => 'w-20'],
                ['label' => 'Type', 'field' => 'type', 'sortable' => true, 'width' => 'w-18'],
                ['label' => 'Status', 'field' => 'status', 'sortable' => false, 'width' => 'w-18'],
                ['label' => 'Requested At', 'field' => 'created_at', 'sortable' => true, 'width' => 'w-16'],
                ['label' => 'Actions', 'field' => 'actions', 'sortable' => false, 'width' => 'w-16'],
            ],
            'sortColumn' => 'created_at',
            'sortDirection' => 'asc',
        ],
        'resolved-requests' => [
            'id' => 'resolved-requests',
            'headers' => [
                ['label' => 'Dataset', 'field' => 'dataset.display_name', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Requested By', 'field' => 'user.email', 'sortable' => true, 'width' => 'w-20'],
                ['label' => 'Type', 'field' => 'type', 'sortable' => true, 'width' => 'w-18'],
                ['label' => 'Status', 'field' => 'status', 'sortable' => true, 'width' => 'w-18'],
                ['label' => 'Reviewed By', 'field' => 'reviewers.email', 'sortable' => true, 'width' => 'w-16'],
                ['label' => 'Comment', 'field' => 'comment', 'sortable' => true, 'width' => 'w-16'],
                ['label' => 'Requested At', 'field' => 'created_at', 'sortable' => true, 'width' => 'w-16'],
            ],
            'sortColumn' => 'display_name',
            'sortDirection' => 'asc',
        ],
        // ADMIN USERS MANAGEMENT
        'user-overview' => [
            'id' => 'user-overview',
            'headers' => [
                ['label' => 'Name', 'field' => 'name', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Email', 'field' => 'email', 'sortable' => true, 'width' => 'w-20'],
                ['label' => 'Role', 'field' => 'role', 'sortable' => true, 'width' => 'w-18'],
                ['label' => 'Status', 'field' => 'status', 'sortable' => false, 'width' => 'w-18'],
                ['label' => 'Datasets', 'field' => 'datasets_count', 'sortable' => true, 'width' => 'w-16'],
                ['label' => 'Actions', 'field' => 'action', 'sortable' => false, 'width' => 'w-16'],
            ],
            'sortColumn' => 'name',
            'sortDirection' => 'asc',
        ],
        'pending-invites' => [
            'id' => 'pending-invites',
            'headers' => [
                ['label' => 'Invited user', 'field' => 'email', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Role', 'field' => 'role', 'sortable' => false, 'width' => 'w-64'],
                ['label' => 'Invite By', 'field' => 'invited_by', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Actions', 'field' => '', 'sortable' => false, 'width' => 'w-64'],
            ],
            'sortColumn' => 'email',
            'sortDirection' => 'asc',
        ],
        'expired-invites' => [
            'id' => 'expired-invites',
            'headers' => [
                ['label' => 'Invited user', 'field' => 'email', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Role', 'field' => 'role', 'sortable' => false, 'width' => 'w-64'],
                ['label' => 'Invite By', 'field' => 'invited_by', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Actions', 'field' => '', 'sortable' => false, 'width' => 'w-64'],
            ],
            'sortColumn' => 'email',
            'sortDirection' => 'asc',
        ],
        // User tables
        'my-requests-pending' => [
            'id' => 'my-requests-pending',
            'headers' => [
                ['label' => 'Dataset', 'field' => 'dataset.display_name', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Type', 'field' => 'type', 'sortable' => true, 'width' => 'w-20'],
                ['label' => 'Status', 'field' => 'status', 'sortable' => true, 'width' => 'w-18'],
                ['label' => 'Requested At', 'field' => 'created_at', 'sortable' => true, 'width' => 'w-16'],
                ['label' => 'Actions', 'field' => 'actions', 'sortable' => false, 'width' => 'w-16'],
            ],
            'sortColumn' => 'created_at',
            'sortDirection' => 'asc',
        ],
        'my-requests-resolved' => [
            'id' => 'my-requests-resolved',
            'headers' => [
                ['label' => 'Dataset', 'field' => 'dataset.display_name', 'sortable' => true, 'width' => 'w-64'],
                ['label' => 'Type', 'field' => 'type', 'sortable' => true, 'width' => 'w-20'],
                ['label' => 'Status', 'field' => 'status', 'sortable' => true, 'width' => 'w-18'],
                ['label' => 'Reviewed By', 'field' => 'reviewers.email', 'sortable' => true, 'width' => 'w-16'],
                ['label' => 'Comment', 'field' => 'comment', 'sortable' => true, 'width' => 'w-16'],
                ['label' => 'Requested At', 'field' => 'created_at', 'sortable' => true, 'width' => 'w-16'],
            ],
            'sortColumn' => 'created_at',
            'sortDirection' => 'asc',
        ],
    ];
}
