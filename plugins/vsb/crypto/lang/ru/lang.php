<?php return [
    'plugin' => [
        'name' => 'Биржи крипты',
        'description' => 'Подключенные биржы криптовалюты',
        'enabled' => 'Активна',
        'market' => 'Биржа',
        'currencies' => 'Валюты',
        'currency' => [
            'rate' => 'Exchange prices',
            'name' => 'Валюта',
            'code' => 'ISO code',
            'from' => 'Base currency',
            'to' => 'Target currency',
        ],
    ],
    'market' => [
        'id' => 'ID',
        'name' => 'Биржа',
        'url' => 'API ссылка',
    ],
    'settings' => [
        'markets' => 'Биржы',
        'markets_tab' => 'Биржи',
        'market' => [
            'name' => 'Наименование',
            'url' => 'API url',
            'sell_spread' => 'Наценка на продажу',
            'buy_spread' => 'Наценка на покупку',
            'wallet_secret' => 'Секретное слово кошелька',
            'wallet_api' => 'АПИ ключ',
        ],
    ],
];