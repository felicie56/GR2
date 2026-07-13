<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Related news generation
    |--------------------------------------------------------------------------
    */

    'limit' => 4,
    'candidate_pool' => 250,
    'minimum_score' => 24,

    /*
    | Điểm số dùng khi so sánh hai bài tin.
    */
    'scores' => [
        'same_category' => 18,
        'entity_match' => 12,
        'title_token_match' => 6,
        'title_to_body_match' => 3,
        'body_token_match' => 0.8,
        'different_source_bonus' => 2,
        'maximum_recency_bonus' => 8,
        'maximum_title_similarity_bonus' => 10,
    ],

    /*
    | Các từ phổ biến không có nhiều giá trị phân biệt chủ đề.
    | Toàn bộ text được chuyển sang chữ thường và bỏ dấu trước khi so sánh.
    */
    'stop_words' => [
        'va', 'la', 'cua', 'cho', 'voi', 'trong', 'tren', 'duoi', 'tu', 'den',
        'mot', 'nhung', 'cac', 'khi', 'thi', 'duoc', 'dang', 'da', 'se', 'co',
        'khong', 'nay', 'do', 'theo', 've', 'tai', 'sau', 'truoc', 'giua',
        'them', 'moi', 'nhieu', 'it', 'rat', 'hon', 'nhat', 'lan', 'qua',
        'thi truong', 'tin tuc', 'bai viet', 'cap nhat', 'nguon tin',

        'the', 'a', 'an', 'and', 'or', 'but', 'for', 'from', 'to', 'of', 'in',
        'on', 'at', 'by', 'with', 'as', 'is', 'are', 'was', 'were', 'be',
        'been', 'being', 'this', 'that', 'these', 'those', 'it', 'its', 'their',
        'his', 'her', 'they', 'them', 'he', 'she', 'we', 'you', 'your', 'our',
        'about', 'after', 'before', 'during', 'into', 'over', 'under', 'through',
        'new', 'latest', 'news', 'report', 'reports', 'says', 'said', 'could',
        'would', 'will', 'may', 'might', 'more', 'most', 'less', 'than',
    ],

    /*
    | Nhóm thực thể/khái niệm quan trọng. Nếu hai bài cùng chứa một thực thể,
    | hệ thống cộng điểm mạnh hơn so với một từ khóa thông thường.
    */
    'entities' => [
        'Bitcoin' => ['bitcoin', 'btc', 'satoshi'],
        'Ethereum' => ['ethereum', 'ether', 'eth'],
        'Solana' => ['solana', 'sol'],
        'XRP' => ['xrp', 'ripple'],
        'BNB' => ['bnb', 'binance coin'],
        'Dogecoin' => ['dogecoin', 'doge'],
        'Cardano' => ['cardano', 'ada'],
        'Avalanche' => ['avalanche', 'avax'],
        'Chainlink' => ['chainlink', 'link'],

        'Bitcoin ETF' => ['bitcoin etf', 'spot bitcoin etf', 'btc etf'],
        'Ethereum ETF' => ['ethereum etf', 'ether etf', 'eth etf'],
        'ETF' => ['exchange traded fund', 'etf'],

        'Stablecoin' => ['stablecoin', 'stable coin'],
        'USDT' => ['usdt', 'tether'],
        'USDC' => ['usdc', 'circle'],
        'DAI' => ['dai stablecoin', 'makerdao dai'],

        'DeFi' => ['defi', 'decentralized finance', 'tai chinh phi tap trung'],
        'NFT' => ['nft', 'non fungible token'],
        'GameFi' => ['gamefi', 'blockchain gaming'],
        'Layer 2' => ['layer 2', 'layer two', 'l2'],
        'RWA' => ['rwa', 'real world asset', 'tai san the gioi thuc'],

        'SEC' => ['sec', 'securities and exchange commission'],
        'Fed' => ['federal reserve', 'fed'],
        'MiCA' => ['mica', 'markets in crypto assets'],
        'Pháp lý' => ['regulation', 'regulatory', 'legal', 'law', 'quy dinh', 'phap ly'],

        'Binance' => ['binance'],
        'Coinbase' => ['coinbase'],
        'Kraken' => ['kraken'],
        'OKX' => ['okx'],
        'Bybit' => ['bybit'],

        'Lạm phát' => ['inflation', 'lam phat'],
        'Lãi suất' => ['interest rate', 'rate cut', 'rate hike', 'lai suat'],
        'Thanh khoản' => ['liquidity', 'thanh khoan'],
        'Dòng tiền' => ['inflow', 'outflow', 'fund flow', 'dong tien'],
        'Khai thác' => ['mining', 'miner', 'hashrate', 'khai thac'],
        'Staking' => ['staking', 'stake', 'validator'],
        'Airdrop' => ['airdrop'],
        'Hack' => ['hack', 'hacker', 'exploit', 'breach', 'tan cong'],
    ],
];