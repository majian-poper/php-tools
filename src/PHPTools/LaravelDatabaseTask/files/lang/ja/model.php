<?php

return [

    'id' => 'ID',
    'created_at' => '作成日時',
    'updated_at' => '更新日時',

    'database_task' => [
        'label' => 'DB タスク',
        'user' => '申請者',
        'task_class' => 'タスク種類',
        'title' => 'タイトル',
        'description' => '説明',
        'risk' => 'リスク',
        'status' => 'ステータス',
        'schedules_at' => '予約実行日時',
        'schedules_at_help_text' => '* 未設定の場合は承認後、即時実行。<br/> * 承認時点で予約時刻を過ぎている場合は即時実行。',

        'inputs' => '入力項目',
        'outputs' => '出力項目',

        'actions' => [
            'request' => [
                'label' => '申請',

                'notifications' => [
                    'requested' => 'DB タスクを申請しました。',
                    'request_failed' => 'DB タスクの申請に失敗しました。',
                ],
            ],

            'preview' => [
                'label' => 'プレビュー',
            ],
        ],
    ],

    'database_task_output' => [
        'label' => 'タスク出力結果',
        'output_value' => '出力内容',
        'expires_at' => '有効期限',

        'actions' => [
            'download' => 'ダウンロード',
        ],
    ],

    'database_task_class' => [
        'label' => '新規 DB タスク',

        'actions' => [
            'create' => '作成',
        ],
    ],

];
