<?php

return [
    'id' => 'ID',
    'created_at' => 'Created at',
    'updated_at' => 'Updated at',
    'count_suffix' => 'Items',

    'approval' => [
        'label' => 'Approval',
        'order_number' => 'Order number',
        'approvable' => 'Approvable',
        'approvable_type' => 'Approvable type',
        'approvable_id' => 'Approvable ID',
        'event' => 'Edit type',
        'old_values' => 'Old values',
        'new_values' => 'New values',
        'now_values' => 'Now values',
        'effected_at' => 'Effected at',
        'rolled_back_at' => 'Rolled back at',
    ],

    'approval_flow' => [
        'label' => 'Approval flow',
        'name' => 'Flow name',
        'approvable_type' => 'Approvable type',
        'flow_type' => 'Flow type',
        'expiration_days' => 'Expiration (days)',
    ],

    'approval_flow_step' => [
        'label' => 'Approval flow step',
        'group_label' => 'Approval group :order',
        'order_number' => 'Order number',
        'approver' => 'Approver',
        'approver_type' => 'Approver type',
        'approver_id' => 'Approver ID',
        'add_approver' => 'Add approver',
    ],

    'approval_step' => [
        'label' => 'Approval step',
        'group_label' => 'Approval group',
        'order_number' => 'Order number',
        'status' => 'Status',
        'approver' => 'Approver',
        'user' => 'User',
        'comment' => 'Comment',
        'approved_at' => 'Approved at',
    ],

    'approval_task' => [
        'label' => 'Approval task',
        'title' => 'Title',
        'description' => 'Description',
        'user' => 'Applicant',
        'flow_type' => 'Approval flow type',
        'status' => 'Status',
        'tenant' => 'Tenant',
        'ip_address' => 'IP address',
        'user_agent' => 'User agent',
        'url' => 'URL',
        'expires_at' => 'Expiration date',
        'approved_at' => 'Approved at',
        'rolled_back_at' => 'Rolled back at',
        'approvals_count' => 'Approvals count',
    ],
];
