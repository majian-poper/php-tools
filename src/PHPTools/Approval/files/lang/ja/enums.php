<?php

return [

    'event' => [
        'CREATING' => '作成',

        'UPDATING' => '更新',

        'RESTORING' => '復旧',

        'TRASHING' => '理論削除',

        'FORCE_DELETING' => '物理削除',
    ],

    'flow-type' => [
        'EVERY' => '全員承認',

        'ANY' => '任意承認',
    ],

    'status' => [

        'PENDING' => '承認待ち',

        'APPROVING' => '承認中',

        'APPROVED' => '承認済み',

        'REJECTED' => '拒否済み',

        'ROLLING_BACK' => 'ロールバック中',

        'ROLLED_BACK' => 'ロールバック済み',
    ],

];
