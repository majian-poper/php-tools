<?php

return [

    'title' => [],

    'inputs' => [],

    'input_types' => [
        'is_file' => 'Upload file',
        'is_excluded' => 'Excluded',

        'query' => [
            'placeholder' => 'e.g., SELECT * FROM users where id = 42;',
            'help_text' => 'Please enter the SQL query to execute.',
        ],

        'number' => [
            'placeholder' => 'e.g., 42',
            'help_text' => 'Please enter a number.',

            'placeholder_multiple' => 'e.g., 12,234,3456',
            'help_text_multiple' => 'Please enter numbers separated by commas (,).',

            'placeholder_file' => 'Please select a file.',
            'help_text_file' => 'Please upload a CSV file containing the ":label" column (numbers).',
        ],

        'select' => [
            'placeholder' => 'Please select ":label".',
            'help_text' => 'Please select ":label" from the options.',

            'placeholder_multiple' => 'Please select ":label".',
            'help_text_multiple' => 'Please select ":label" from the options, multiple selections allowed.',
        ],

        'datetime' => [
            'placeholder' => '',
            'help_text' => 'Please enter a date and time',
        ],

        'boolean' => [
            'placeholder' => '',
            'help_text' => 'Checking this will be treated as YES, unchecking as NO.',

            'true' => 'Yes',
            'false' => 'No',
        ],

        'file' => [
            'placeholder' => 'Please select a file.',
            'help_text' => 'Please upload a file for ":label".',
        ],
    ],

    'errors' => [
        'task_class_not_found' => 'Task type [:task_class] not found.',
        'task_status_update_failed' => 'Failed to update task status.',
        'task_not_batchable' => 'Task is not batchable.',
        'no_batchable_inputs' => 'No batchable inputs found.',
        'output_not_batchable' => 'Output is not batchable.',
        'output_batch_order_mismatch' => 'Batch order of the output does not match the job.',
        'merge_batchable_outputs_failed' => 'Failed to merge batchable outputs.',
        'output_should_not_be_batchable' => 'Output should not be batchable.',
        'no_data' => 'No data found for the task.',
    ],

];
