<?php

return [

    'id' => 'ID',
    'created_at' => 'Created at',
    'updated_at' => 'Updated at',

    'database_task' => [
        'label' => 'DB task',
        'user' => 'Requesting user',
        'task_class' => 'Task type',
        'title' => 'Title',
        'description' => 'Description',
        'risk' => 'Risk',
        'status' => 'Status',
        'schedules_at' => 'Schedules at',
        'schedules_at_help_text' => '* If not set, it will be executed immediately after approved.<br/> * If the scheduled time has passed at the time of approve, it will be executed immediately.',

        'inputs' => 'Input items',
        'outputs' => 'Output items',

        'actions' => [
            'request' => [
                'label' => 'Request',

                'notifications' => [
                    'requested' => 'DB task has been requested.',
                    'request_failed' => 'Failed to request DB task.',
                ],
            ],

            'preview' => [
                'label' => 'Preview',
            ],
        ],
    ],

    'database_task_output' => [
        'label' => 'Task output',
        'output_value' => 'Output value',
        'expires_at' => 'Expires at',

        'actions' => [
            'download' => 'Download',
        ],
    ],

    'database_task_class' => [
        'label' => 'New DB task',

        'actions' => [
            'create' => 'Create',
        ],
    ],

];
