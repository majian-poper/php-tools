<?php

return [
    'actions' => [

        'approve' => [
            'single' => [
                'label' => 'Approve',

                'modal' => [
                    'heading' => 'Approve :label',
                    'form' => [
                        'comment' => 'Approve comment',
                    ],
                    'actions' => [
                        'approve' => [
                            'label' => 'Approve',
                        ],
                    ],
                ],

                'notifications' => [
                    'success' => [
                        'title' => 'Approval approved',
                    ],
                ],
            ],
        ],

        'reject' => [
            'single' => [
                'label' => 'Reject',

                'modal' => [
                    'heading' => 'Reject :label',
                    'form' => [
                        'comment' => 'Reject comment',
                    ],
                    'actions' => [
                        'reject' => [
                            'label' => 'Reject',
                        ],
                    ],
                ],

                'notifications' => [
                    'success' => [
                        'title' => 'Approval rejected',
                    ],
                ],
            ],

        ],

        'rollback' => [
            'single' => [
                'label' => 'Rollback',

                'modal' => [
                    'heading' => 'Rollback :label',
                    'actions' => [
                        'rollback' => [
                            'label' => 'Rollback',
                        ],
                    ],
                ],

                'notifications' => [
                    'success' => [
                        'title' => 'Approval rolled back',
                    ],
                ],
            ],
        ],

        'request' => [
            'single' => [
                'label' => 'Request',

                'modal' => [
                    'heading' => 'Request for :event :label',
                    'actions' => [
                        'request' => [
                            'label' => 'Request',
                        ],
                    ],
                ],

                'notifications' => [
                    'success' => [
                        'title' => 'Approval requested',
                    ],
                ],
            ],
        ],

    ],
];
