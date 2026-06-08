<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RSS SSL Verify
    |--------------------------------------------------------------------------
    | Local Windows đôi khi thiếu CA certificate nên cURL báo lỗi SSL.
    | Local có thể set NEWS_RSS_VERIFY_SSL=false trong .env.
    */

    'verify_ssl' => env('NEWS_RSS_VERIFY_SSL', false),

    /*
    |--------------------------------------------------------------------------
    | Auto Translation
    |--------------------------------------------------------------------------
    | google_free: dùng endpoint dịch miễn phí để demo local.
    | none: không dịch, chỉ giữ nguyên tiếng Anh.
    */

    'translate_to_vietnamese' => env('NEWS_TRANSLATE_TO_VIETNAMESE', true),

    'translation_driver' => env('NEWS_TRANSLATION_DRIVER', 'google_free'),

    /*
    |--------------------------------------------------------------------------
    | RSS Sources
    |--------------------------------------------------------------------------
    */

    'sources' => [
        [
            'name' => 'CoinDesk',
            'feed_url' => 'https://www.coindesk.com/arc/outboundfeeds/rss/',
            'default_category' => 'Thị trường',
            'enabled' => true,
        ],
        [
            'name' => 'Cointelegraph',
            'feed_url' => 'https://cointelegraph.com/rss',
            'default_category' => 'Tin tức Crypto',
            'enabled' => true,
        ],
        [
            'name' => 'Decrypt',
            'feed_url' => 'https://decrypt.co/feed',
            'default_category' => 'Tin tức Crypto',
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Category Keyword Mapping
    |--------------------------------------------------------------------------
    | Command sẽ dùng cả bản tiếng Anh gốc + bản dịch để gán category.
    */

    'category_keywords' => [
        'Bitcoin' => [
            'bitcoin',
            'btc',
            'satoshi',
            'spot bitcoin etf',
        ],

        'Ethereum' => [
            'ethereum',
            'eth',
            'ether',
            'layer 2',
            'l2',
            'arbitrum',
            'optimism',
            'base',
        ],

        'DeFi' => [
            'defi',
            'staking',
            'yield',
            'lending',
            'borrow',
            'dex',
            'uniswap',
            'aave',
            'curve',
        ],

        'Stablecoin' => [
            'stablecoin',
            'stable coin',
            'usdt',
            'usdc',
            'tether',
            'circle',
            'dai',
        ],

        'NFT / GameFi' => [
            'nft',
            'gamefi',
            'gaming',
            'metaverse',
            'collectible',
        ],

        'Pháp lý' => [
            'sec',
            'regulation',
            'regulatory',
            'legal',
            'law',
            'policy',
            'compliance',
            'court',
            'lawsuit',
            'quy định',
            'pháp lý',
            'tòa án',
        ],

        'Thị trường' => [
            'market',
            'price',
            'trading',
            'trader',
            'bull',
            'bear',
            'rally',
            'crash',
            'etf',
            'fed',
            'inflation',
            'thị trường',
            'giá',
            'giao dịch',
            'lạm phát',
        ],

        'Blockchain' => [
            'blockchain',
            'web3',
            'protocol',
            'network',
            'mainnet',
            'validator',
            'node',
            'chuỗi khối',
            'mạng lưới',
            'giao thức',
        ],

        'AI' => [
            'ai',
            'artificial intelligence',
            'machine learning',
            'trí tuệ nhân tạo',
        ],

        'Tin tức Crypto' => [
            'crypto',
            'cryptocurrency',
            'token',
            'altcoin',
            'exchange',
            'binance',
            'coinbase',
            'kraken',
            'tiền mã hóa',
            'tiền điện tử',
            'sàn giao dịch',
        ],
    ],
];