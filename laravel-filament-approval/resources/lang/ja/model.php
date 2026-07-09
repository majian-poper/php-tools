<?php

return [
    'id' => 'ID',
    'created_at' => '作成日時',
    'updated_at' => '更新日時',
    'count_suffix' => '件',

    'approval' => [
        'label' => '申請',
        'order_number' => '順番',
        'approvable' => '承認対象',
        'approvable_type' => '承認対象タイプ',
        'approvable_id' => '承認対象ID',
        'event' => '編集タイプ',
        'old_values' => '旧値',
        'new_values' => '新値',
        'now_values' => '現時点の値',
        'effected_at' => '反映日時',
        'rolled_back_at' => 'ロールバック日時',
    ],

    'approval_flow' => [
        'label' => '承認フロー',
        'name' => 'フロー名',
        'approvable_type' => '承認対象タイプ',
        'flow_type' => 'フロータイプ',
        'expiration_days' => '有効期限(日)',
    ],

    'approval_flow_step' => [
        'label' => '承認フローステップ',
        'group_label' => '承認グループ :order',
        'order_number' => '順番',
        'approver' => '承認者',
        'approver_type' => '承認者タイプ',
        'approver_id' => '承認者ID',
        'add_approver' => '承認者を追加',
    ],

    'approval_step' => [
        'label' => '承認ステップ',
        'group_label' => '承認グループ',
        'order_number' => '順番',
        'status' => 'ステータス',
        'approver' => '承認者',
        'user' => '承認実行者',
        'comment' => 'コメント',
        'approved_at' => '承認日時',
    ],

    'approval_task' => [
        'label' => '申請承認',
        'title' => 'タイトル',
        'description' => '説明',
        'user' => '申請者',
        'flow_type' => '承認フロータイプ',
        'status' => 'ステータス',
        'tenant' => 'テナント',
        'ip_address' => 'IPアドレス',
        'user_agent' => 'ユーザーエージェント',
        'url' => 'URL',
        'expires_at' => '有効期限',
        'approved_at' => '承認日時',
        'rolled_back_at' => 'ロールバック日時',
        'approvals_count' => '申請件数',
    ],
];
