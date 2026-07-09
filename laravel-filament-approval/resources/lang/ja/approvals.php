<?php

return [
    'actions' => [

        'approve' => [
            'single' => [
                'label' => '承認',

                'modal' => [
                    'heading' => ':label を承認する',
                    'form' => [
                        'comment' => '承認コメント',
                    ],
                    'actions' => [
                        'approve' => [
                            'label' => '承認',
                        ],
                    ],
                ],

                'notifications' => [
                    'success' => [
                        'title' => '承認されました',
                    ],
                ],
            ],
        ],

        'reject' => [
            'single' => [
                'label' => '拒否',

                'modal' => [
                    'heading' => ':label を拒否する',
                    'form' => [
                        'comment' => '拒否コメント',
                    ],
                    'actions' => [
                        'reject' => [
                            'label' => '拒否',
                        ],
                    ],
                ],

                'notifications' => [
                    'success' => [
                        'title' => '拒否されました',
                    ],
                ],
            ],

        ],

        'rollback' => [
            'single' => [
                'label' => 'ロールバック',

                'modal' => [
                    'heading' => ':label をロールバックする',
                    'actions' => [
                        'rollback' => [
                            'label' => 'ロールバック',
                        ],
                    ],
                ],

                'notifications' => [
                    'success' => [
                        'title' => 'ロールバックされました',
                    ],
                ],
            ],
        ],

        'request' => [
            'single' => [
                'label' => '申請',

                'modal' => [
                    'heading' => ':labelの:eventを申請する',
                    'actions' => [
                        'request' => [
                            'label' => '申請',
                        ],
                    ],
                ],

                'notifications' => [
                    'success' => [
                        'title' => '申請されました',
                    ],
                ],
            ],
        ],

    ],
];
