<?php return [
    'plugin' => [
        'name' => 'Payment module',
        'description' => 'Plugin description.',
        'manager' => 'Manager',
    ],
    'settings' => [
        'endpoint_tab' => 'Merchants',
        'endpoint' => [
            'current' => 'Current endpoint',
            'version' => 'Protocol version',
        ],
        'responsepage' => 'Page with Response component',
        'callbackpage' => 'Page with Callback component',
        'cardregister_tab' => 'Card register',
        'transfer_tab' => 'Transfer settings',
    ],
    'cardpool' => [
        'title' => 'Card pool',
        'cardname' => 'Card name',
        'cardref' => 'Card reference',
        'daily' => 'Daily limit',
        'monthly' => 'Monthly limit',
        'enabled' => 'Enabled',
    ],
    'transaction' => [
        'title' => 'Transaction list',
        'type' => 'Transaction type',
        'endpoint' => 'Merchant',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'code' => 'Transaction code',
        'card_id' => 'Card',
        'id' => 'Transaction ID',
        'parent_id' => 'Original transaction ID',
        'created_at' => 'Date',
        'update_at' => 'Updated',
    ],
    'message'=> [
        'total' => 'Total'
    ]
];
