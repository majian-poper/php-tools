<?php

return [

    'title' => [],

    'inputs' => [],

    'input_types' => [
        'is_file' => 'ファイルをアップロードする',
        'is_excluded' => '対象外になる',

        'query' => [
            'placeholder' => '例: SELECT * FROM users where id = 42;',
            'help_text' => '実行する SQL クエリを入力してください。',
        ],

        'number' => [
            'placeholder' => '例: 42',
            'help_text' => '半角数字を入力してください。',

            'placeholder_multiple' => '例: 12,234,3456',
            'help_text_multiple' => '半角数字をカンマ（,）区切りで入力してください。',

            'placeholder_file' => 'ファイルを選択してください。',
            'help_text_file' => '「:label」カラム（半角数字）が含まれるCSVファイルをアップロードしてください。',
        ],

        'select' => [
            'placeholder' => '「:label」を選択してください。',
            'help_text' => 'オプションから「:label」を選択してください。',

            'placeholder_multiple' => '「:label」を選択してください。',
            'help_text_multiple' => 'オプションから「:label」を選択してください、複数選択可能です。',
        ],

        'datetime' => [
            'placeholder' => '',
            'help_text' => '日付と時間を入力してください',
        ],

        'boolean' => [
            'placeholder' => '',
            'help_text' => 'チェックを入れると「はい」、外すと「いいえ」として扱われます。',

            'true' => 'はい',
            'false' => 'いいえ',
        ],

        'file' => [
            'placeholder' => 'ファイルを選択してください。',
            'help_text' => '「:label」用のファイルをアップロードしてください。',
        ],
    ],

    'errors' => [
        'task_class_not_found' => 'タスクタイプが見つかりませんでした。',
        'task_status_update_failed' => 'タスクステータスの更新に失敗しました。',
        'task_not_batchable' => 'タスクはバッチ処理不可です。',
        'no_batchable_inputs' => 'バッチ処理可能な入力が見つかりませんでした。',
        'output_not_batchable' => '出力データはバッチ処理できません。',
        'output_batch_order_mismatch' => '出力データのバッチ順序がジョブと一致しません。',
        'merge_batchable_outputs_failed' => '出力データのマージ操作が失敗しました。',
        'output_should_not_be_batchable' => '出力データはバッチ処理に適していません。',
        'no_data' => '該当するデータがありませんでした。',
    ],

];
