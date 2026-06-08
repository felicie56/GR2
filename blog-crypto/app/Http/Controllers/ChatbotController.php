<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\ChatbotFaq;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:500'],
        ]);

        $message = trim($validated['message']);
        $normalizedMessage = $this->normalizeText($message);
        $expandedMessage = $this->expandMessageWithSynonyms($normalizedMessage);

        $intent = $this->detectIntent($normalizedMessage);

        /**
         * 0. Nếu user spam/chửi bot thì trả lời lịch sự, không cố suy đoán crypto.
         */
        if ($intent === 'abuse') {
            return response()->json([
                'success' => true,
                'answer' => 'Mình sẽ cố gắng hỗ trợ tốt hơn. Hiện tại mình được thiết kế để trả lời các câu hỏi liên quan đến crypto, giá coin, blog, tin tức, tác giả, bình luận và cách dùng website. Bạn có thể hỏi lại theo một chủ đề cụ thể hơn nhé.',
                'matched_question' => null,
                'category' => 'abuse',
                'confidence' => 'controlled',
                'related_links' => [],
                'suggestions' => [
                    'Bitcoin là gì?',
                    'Làm sao để trở thành tác giả?',
                    'Có nên chia sẻ private key không?',
                ],
            ]);
        }

        /**
         * 1. Nếu câu hỏi nằm ngoài phạm vi website/crypto thì từ chối mềm.
         */
        if ($intent === 'out_of_scope') {
            return response()->json([
                'success' => true,
                'answer' => 'Mình hiện được thiết kế để hỗ trợ các câu hỏi liên quan đến crypto, tài chính cá nhân, cách dùng website, blog, tin tức, tác giả, bình luận và quản trị hệ thống. Câu hỏi này có vẻ nằm ngoài phạm vi đó, nên mình chưa thể trả lời chính xác.',
                'matched_question' => null,
                'category' => 'out_of_scope',
                'confidence' => 'controlled',
                'related_links' => [],
                'suggestions' => [
                    'Bitcoin là gì?',
                    'Làm sao để trở thành tác giả?',
                    'Có nên chia sẻ private key không?',
                ],
            ]);
        }

        $relatedLinks = $this->getRelatedContent($normalizedMessage);

        /**
         * 2. Nếu hỏi về giá coin / coin cụ thể thì ưu tiên tra DB crypto.
         */
        if (in_array($intent, ['coin_price', 'coin_lookup'], true)) {
            $coinAnswer = $this->handleCoinQuestion($normalizedMessage);

            if ($coinAnswer !== null) {
                return response()->json([
                    'success' => true,
                    'answer' => $coinAnswer['answer'],
                    'matched_question' => null,
                    'category' => 'crypto_coin',
                    'confidence' => $coinAnswer['confidence'],
                    'related_links' => $relatedLinks,
                    'suggestions' => [
                        'Rủi ro khi đầu tư crypto là gì?',
                        'Stablecoin là gì?',
                        'Làm sao để xem giá crypto?',
                    ],
                ]);
            }
        }

        /**
         * 3. Nếu hỏi tìm bài/tin thì ưu tiên Blog/News retrieval.
         */
        if ($intent === 'content_search' && count($relatedLinks) > 0) {
            return response()->json([
                'success' => true,
                'answer' => 'Mình tìm thấy một số nội dung liên quan trong hệ thống. Bạn có thể mở các link bên dưới để đọc chi tiết. Nếu muốn tìm chính xác hơn, hãy hỏi cụ thể hơn theo tên coin, chủ đề hoặc chuyên mục.',
                'matched_question' => null,
                'category' => 'content_search',
                'confidence' => 'medium',
                'related_links' => $relatedLinks,
                'suggestions' => [
                    'Có bài viết nào về Bitcoin không?',
                    'Có tin tức nào về DeFi không?',
                    'Làm sao để tìm kiếm bài viết?',
                ],
            ]);
        }

        /**
         * 4. FAQ matching.
         */
        $topMatches = $this->getTopFaqMatches($expandedMessage);
        $bestMatch = $topMatches[0] ?? null;

        if ($bestMatch && $bestMatch['score'] >= 6) {
            $faq = $bestMatch['faq'];

            return response()->json([
                'success' => true,
                'answer' => $faq->answer,
                'matched_question' => $faq->question,
                'category' => $faq->category,
                'confidence' => 'high',
                'related_links' => $relatedLinks,
                'suggestions' => $this->getSuggestions($faq->category),
            ]);
        }

        /**
         * 5. Nếu match vừa phải thì trả lời tổng hợp nhiều FAQ gần đúng.
         */
        if ($bestMatch && $bestMatch['score'] >= 3) {
            $answerParts = [];
            $usedQuestions = [];

            foreach (array_slice($topMatches, 0, 3) as $match) {
                if ($match['score'] < 3) {
                    continue;
                }

                $faq = $match['faq'];

                if (in_array($faq->question, $usedQuestions, true)) {
                    continue;
                }

                $usedQuestions[] = $faq->question;
                $answerParts[] = "• {$faq->question}\n{$faq->answer}";
            }

            $combinedAnswer = "Mình chưa chắc 100% ý bạn hỏi, nhưng câu hỏi của bạn có vẻ liên quan đến các nội dung sau:\n\n"
                . implode("\n\n", $answerParts)
                . "\n\nBạn có thể hỏi cụ thể hơn để mình trả lời sát hơn nhé.";

            return response()->json([
                'success' => true,
                'answer' => $combinedAnswer,
                'matched_question' => $bestMatch['faq']->question,
                'category' => $bestMatch['faq']->category,
                'confidence' => 'medium',
                'related_links' => $relatedLinks,
                'suggestions' => $this->getSuggestions($bestMatch['faq']->category),
            ]);
        }

        /**
         * 6. Nếu không match FAQ nhưng vẫn có nội dung liên quan trong Blog/News.
         */
        if (count($relatedLinks) > 0) {
            return response()->json([
                'success' => true,
                'answer' => 'Mình chưa có FAQ chính xác cho câu hỏi này, nhưng mình tìm thấy một số bài viết hoặc tin tức có thể liên quan trong hệ thống. Bạn có thể xem các nội dung bên dưới.',
                'matched_question' => null,
                'category' => 'content_related',
                'confidence' => 'low',
                'related_links' => $relatedLinks,
                'suggestions' => $this->getSuggestions('fallback'),
            ]);
        }

        /**
         * 7. Fallback theo intent.
         */
        return response()->json([
            'success' => true,
            'answer' => $this->buildFallbackAnswer($normalizedMessage, $intent),
            'matched_question' => null,
            'category' => $intent,
            'confidence' => 'low',
            'related_links' => [],
            'suggestions' => $this->getSuggestions($intent),
        ]);
    }

    private function detectIntent(string $text): string
{
    /**
     * Chửi bot / spam / câu không có ý định hỏi nghiêm túc.
     */
    if ($this->isAbusiveMessage($text)) {
        return 'abuse';
    }

    /**
     * Chỉ đánh out_of_scope nếu thật sự rõ là ngoài phạm vi.
     */
    if ($this->isClearlyOutOfScope($text)) {
        return 'out_of_scope';
    }

    /**
     * Câu hỏi giá coin: chỉ nhận nếu có keyword giá + xác định được coin cụ thể.
     */
    if (
        $this->containsAny($text, [
            'gia',
            'price',
            'bao nhieu',
            'usd',
            'tang',
            'giam',
            'market cap',
            'von hoa',
            'volume',
            'khoi luong',
        ])
        && $this->findCoinFromMessage($text) !== null
    ) {
        return 'coin_price';
    }

    /**
     * Câu hỏi về coin cụ thể, nhưng không nhất thiết hỏi giá.
     */
    if ($this->looksLikeCoinQuestion($text)) {
        return 'coin_lookup';
    }

    if ($this->containsAny($text, [
        'co bai',
        'bai nao',
        'tin nao',
        'tim bai',
        'tim tin',
        'noi dung',
        'doc ve',
        'bai viet ve',
        'tin tuc ve',
        'news ve',
        'blog ve',
    ])) {
        return 'content_search';
    }

    /**
     * AUTHOR / BLOG WORKFLOW
     * Thêm các cụm như "bài viết chưa hiển thị", "chưa public", "chờ duyệt".
     */
    if ($this->containsAny($text, [
        'author',
        'tac gia',
        'viet bai',
        'dang bai',
        'bai viet',
        'bai cua toi',
        'bai viet cua toi',
        'chua hien thi',
        'khong hien thi',
        'bai chua hien',
        'bai khong hien',
        'chua public',
        'chua duoc public',
        'chua duoc hien thi',
        'cho duyet',
        'dang cho duyet',
        'trang thai bai',
        'phe duyet',
        'duyet bai',
        'bi tu choi',
        'reject',
        'rejected',
        'approved',
    ])) {
        return 'author';
    }

    if ($this->containsAny($text, [
        'admin',
        'dashboard',
        'quan tri',
        'duyet tac gia',
        'duyet bai',
        'quan ly tin',
        'quan ly binh luan',
    ])) {
        return 'admin';
    }

    if ($this->containsAny($text, [
        'comment',
        'binh luan',
        'xoa binh luan',
        'phan hoi',
    ])) {
        return 'comment';
    }

    if ($this->containsAny($text, [
        'scam',
        'lua dao',
        'private key',
        'seed phrase',
        'bao mat',
        'loi nhuan',
        'cam ket',
        'airdrop',
        'mat tien',
        'risk',
        'rui ro',
    ])) {
        return 'risk';
    }

    if ($this->containsAny($text, [
        'bitcoin',
        'btc',
        'ethereum',
        'eth',
        'crypto',
        'blockchain',
        'defi',
        'stablecoin',
        'altcoin',
        'web3',
        'smart contract',
        'nft',
        'layer 2',
        'airdrop',
        'wallet',
        'vi crypto',
    ])) {
        return 'crypto_education';
    }

    if ($this->containsAny($text, [
        'website',
        'dang ky',
        'dang nhap',
        'ho so',
        'doc blog',
        'xem tin',
        'tim kiem',
        'category',
        'chuyen muc',
    ])) {
        return 'website';
    }

    /**
     * Quan trọng:
     * Không trả out_of_scope ở đây nữa.
     * Vì có thể câu hỏi vẫn match FAQ, nhưng intent chưa detect được.
     */
    return 'unknown';
}

    private function isAbusiveMessage(string $text): bool
    {
        $tokens = $this->simpleTokens($text);

        $abusiveTokens = [
            'ngu',
            'stupid',
            'dumb',
            'idiot',
            'fuck',
            'shit',
            'dm',
            'dmm',
            'vl',
            'vcl',
        ];

        foreach ($abusiveTokens as $token) {
            if (in_array($token, $tokens, true)) {
                return true;
            }
        }

        $abusivePhrases = [
            'ngu the',
            'bot ngu',
            'may ngu',
            'ngu vl',
            'oc cho',
            'do ngu',
            'tra loi ngu',
            'bot nhu cut',
        ];

        foreach ($abusivePhrases as $phrase) {
            if (str_contains($text, $this->normalizeText($phrase))) {
                return true;
            }
        }

        return false;
    }

    private function isClearlyOutOfScope(string $text): bool
    {
        $tokens = $this->simpleTokens($text);

        /**
         * Bóng đá / thể thao.
         * Dùng token exact để tránh "mu" trong "muốn" bị bắt nhầm.
         */
        $sportsTokens = [
            'mu',
            'ars',
            'arsenal',
            'chelsea',
            'liverpool',
            'mancity',
            'barca',
            'real',
            'madrid',
            'psg',
            'ronaldo',
            'messi',
        ];

        foreach ($sportsTokens as $token) {
            if (in_array($token, $tokens, true)) {
                return true;
            }
        }

        $outOfScopePhrases = [
            'bong da',
            'ngoai hang anh',
            'premier league',
            'man united',
            'man utd',
            'real madrid',
            'du doan ti so',
            'ti so',
            'thoi tiet',
            'weather',
            'nau an',
            'nau pho',
            'mon an',
            'phim gi',
            'am nhac',
            'bai hat',
            'du lich',
            've may bay',
            'khach san',
            'game nao',
            'lap trinh python',
            'giai toan',
            'lich su viet nam',
        ];

        foreach ($outOfScopePhrases as $phrase) {
            if (str_contains($text, $this->normalizeText($phrase))) {
                return true;
            }
        }

        /**
         * Pattern kiểu "mu vs ars", "mu voi arsenal".
         */
        if (
            (in_array('mu', $tokens, true) || in_array('ars', $tokens, true) || in_array('arsenal', $tokens, true))
            && (
                in_array('vs', $tokens, true)
                || in_array('voi', $tokens, true)
                || in_array('gap', $tokens, true)
                || in_array('tran', $tokens, true)
            )
        ) {
            return true;
        }

        return false;
    }

    private function handleCoinQuestion(string $normalizedMessage): ?array
    {
        $coinInfo = $this->findCoinFromMessage($normalizedMessage);

        if ($coinInfo === null) {
            return [
                'confidence' => 'low',
                'answer' => 'Mình nhận thấy bạn đang hỏi về coin hoặc giá crypto, nhưng chưa xác định được coin cụ thể. Bạn có thể hỏi rõ hơn như “giá BTC”, “ETH là gì?” hoặc “Solana giá bao nhiêu?”.',
            ];
        }

        $coinRow = $coinInfo['row'] ?? null;
        $symbol = $coinInfo['symbol'] ?? null;
        $name = $coinInfo['name'] ?? null;

        if ($coinRow === null) {
            return [
                'confidence' => 'low',
                'answer' => "Mình nhận ra bạn đang hỏi về {$name}" . ($symbol ? " ({$symbol})" : '') . ", nhưng hiện database của website chưa có dữ liệu cho coin này. Bạn có thể kiểm tra lại trong mục “Giá Crypto” hoặc cập nhật danh sách coin trong hệ thống.",
            ];
        }

        $symbol = $this->getValueFromRow($coinRow, ['symbol', 'coin_symbol', 'ticker', 'code']) ?? $symbol;
        $name = $this->getValueFromRow($coinRow, ['name', 'coin_name', 'full_name']) ?? $name ?? $symbol;
        $coinId = $this->getValueFromRow($coinRow, ['id']);

        $latestPrice = $this->findLatestPriceForCoin($coinId, $symbol);

        if ($latestPrice === null) {
            return [
                'confidence' => 'medium',
                'answer' => "Hệ thống có theo dõi {$name}" . ($symbol ? " ({$symbol})" : '') . ", nhưng hiện chưa tìm thấy dữ liệu giá mới nhất trong bảng giá. Bạn có thể chạy command cập nhật giá crypto hoặc kiểm tra trang “Giá Crypto”.",
            ];
        }

        $price = $latestPrice['price'];
        $change = $latestPrice['change'];
        $marketCap = $latestPrice['market_cap'];
        $time = $latestPrice['time'];

        $answer = "Hệ thống đang ghi nhận {$name}" . ($symbol ? " ({$symbol})" : '') . " với giá khoảng {$this->formatPrice($price)} USD.";

        if ($change !== null) {
            $direction = (float) $change >= 0 ? 'tăng' : 'giảm';
            $answer .= " Biến động 24h khoảng {$direction} " . number_format(abs((float) $change), 2) . "%.";
        }

        if ($marketCap !== null) {
            $answer .= " Vốn hóa thị trường khoảng {$this->formatLargeNumber($marketCap)} USD.";
        }

        if ($time !== null) {
            $answer .= " Dữ liệu được cập nhật gần nhất lúc {$this->formatDateTime($time)}.";
        }

        $answer .= " Lưu ý: giá crypto biến động mạnh và thông tin này chỉ mang tính tham khảo, không phải lời khuyên đầu tư.";

        return [
            'confidence' => 'high',
            'answer' => $answer,
        ];
    }

    private function findCoinFromMessage(string $text): ?array
    {
        $aliases = [
            ['symbol' => 'BTC', 'name' => 'Bitcoin', 'terms' => ['btc', 'bitcoin']],
            ['symbol' => 'ETH', 'name' => 'Ethereum', 'terms' => ['eth', 'ethereum']],
            ['symbol' => 'BNB', 'name' => 'BNB', 'terms' => ['bnb', 'binance coin']],
            ['symbol' => 'SOL', 'name' => 'Solana', 'terms' => ['sol', 'solana']],
            ['symbol' => 'XRP', 'name' => 'XRP', 'terms' => ['xrp', 'ripple']],
            ['symbol' => 'ADA', 'name' => 'Cardano', 'terms' => ['ada', 'cardano']],
            ['symbol' => 'DOGE', 'name' => 'Dogecoin', 'terms' => ['doge', 'dogecoin']],
            ['symbol' => 'AVAX', 'name' => 'Avalanche', 'terms' => ['avax', 'avalanche']],
            ['symbol' => 'DOT', 'name' => 'Polkadot', 'terms' => ['dot', 'polkadot']],
            ['symbol' => 'LINK', 'name' => 'Chainlink', 'terms' => ['link', 'chainlink']],
            ['symbol' => 'TRX', 'name' => 'TRON', 'terms' => ['trx', 'tron']],
            ['symbol' => 'LTC', 'name' => 'Litecoin', 'terms' => ['ltc', 'litecoin']],
            ['symbol' => 'MATIC', 'name' => 'Polygon', 'terms' => ['matic', 'polygon']],
            ['symbol' => 'USDT', 'name' => 'Tether', 'terms' => ['usdt', 'tether']],
            ['symbol' => 'USDC', 'name' => 'USD Coin', 'terms' => ['usdc', 'usd coin']],
        ];

        /**
         * Match alias bằng exact token.
         * Ví dụ:
         * - "sol giá bao nhiêu" match SOL
         * - "ngu thế" không bị match linh tinh
         */
        foreach ($aliases as $alias) {
            foreach ($alias['terms'] as $term) {
                $normalizedTerm = $this->normalizeText($term);

                $isMatched = str_contains($normalizedTerm, ' ')
                    ? str_contains($text, $normalizedTerm)
                    : $this->hasExactToken($text, $normalizedTerm);

                if ($isMatched) {
                    $row = $this->findCoinRow($alias['symbol'], $alias['name']);

                    return [
                        'symbol' => $alias['symbol'],
                        'name' => $alias['name'],
                        'row' => $row,
                    ];
                }
            }
        }

        if (! Schema::hasTable('crypto_coins')) {
            return null;
        }

        $coins = DB::table('crypto_coins')->limit(500)->get();

        foreach ($coins as $coin) {
            $symbol = $this->getValueFromRow($coin, ['symbol', 'coin_symbol', 'ticker', 'code']);
            $name = $this->getValueFromRow($coin, ['name', 'coin_name', 'full_name']);

            $normalizedSymbol = $symbol ? $this->normalizeText((string) $symbol) : '';
            $normalizedName = $name ? $this->normalizeText((string) $name) : '';

            /**
             * Không match symbol 1 ký tự như U, vì rất dễ sai.
             */
            $symbolMatched = false;

            if ($normalizedSymbol && mb_strlen($normalizedSymbol) >= 2) {
                $symbolMatched = $this->hasExactToken($text, $normalizedSymbol);
            }

            /**
             * Name phải dài từ 3 ký tự trở lên và match theo cụm.
             */
            $nameMatched = false;

            if ($normalizedName && mb_strlen($normalizedName) >= 3) {
                $nameMatched = str_contains($text, $normalizedName);
            }

            if ($symbolMatched || $nameMatched) {
                return [
                    'symbol' => $symbol,
                    'name' => $name,
                    'row' => $coin,
                ];
            }
        }

        return null;
    }

    private function findCoinRow(?string $symbol, ?string $name): ?object
    {
        if (! Schema::hasTable('crypto_coins')) {
            return null;
        }

        $query = DB::table('crypto_coins');

        $query->where(function ($subQuery) use ($symbol, $name) {
            if ($symbol) {
                foreach (['symbol', 'coin_symbol', 'ticker', 'code'] as $column) {
                    if (Schema::hasColumn('crypto_coins', $column)) {
                        $subQuery->orWhere($column, $symbol);
                    }
                }
            }

            if ($name) {
                foreach (['name', 'coin_name', 'full_name'] as $column) {
                    if (Schema::hasColumn('crypto_coins', $column)) {
                        $subQuery->orWhere($column, 'like', '%' . $name . '%');
                    }
                }
            }
        });

        return $query->first();
    }

    private function findLatestPriceForCoin($coinId, ?string $symbol): ?array
    {
        if (! Schema::hasTable('crypto_prices')) {
            return null;
        }

        $priceColumn = $this->firstExistingColumn('crypto_prices', [
            'price_usd',
            'current_price',
            'usd_price',
            'price',
            'last_price',
        ]);

        if ($priceColumn === null) {
            return null;
        }

        $query = DB::table('crypto_prices');

        $fkColumn = $this->firstExistingColumn('crypto_prices', [
            'crypto_coin_id',
            'coin_id',
            'cryptocoin_id',
        ]);

        $symbolColumn = $this->firstExistingColumn('crypto_prices', [
            'symbol',
            'coin_symbol',
            'ticker',
        ]);

        if ($fkColumn && $coinId) {
            $query->where($fkColumn, $coinId);
        } elseif ($symbolColumn && $symbol) {
            $query->where($symbolColumn, $symbol);
        } else {
            return null;
        }

        $timeColumn = $this->firstExistingColumn('crypto_prices', [
            'created_at',
            'updated_at',
            'fetched_at',
            'recorded_at',
            'price_time',
            'timestamp',
        ]);

        if ($timeColumn) {
            $query->orderByDesc($timeColumn);
        } elseif (Schema::hasColumn('crypto_prices', 'id')) {
            $query->orderByDesc('id');
        }

        $row = $query->first();

        if (! $row) {
            return null;
        }

        $changeColumn = $this->firstExistingColumn('crypto_prices', [
            'price_change_percentage_24h',
            'percent_change_24h',
            'change_24h',
            'price_change_24h',
        ]);

        $marketCapColumn = $this->firstExistingColumn('crypto_prices', [
            'market_cap',
            'market_cap_usd',
            'marketcap',
        ]);

        return [
            'price' => $this->getValueFromRow($row, [$priceColumn]),
            'change' => $changeColumn ? $this->getValueFromRow($row, [$changeColumn]) : null,
            'market_cap' => $marketCapColumn ? $this->getValueFromRow($row, [$marketCapColumn]) : null,
            'time' => $timeColumn ? $this->getValueFromRow($row, [$timeColumn]) : null,
        ];
    }

    private function looksLikeCoinQuestion(string $text): bool
    {
        /**
         * Không dùng regex đoán mọi cụm 2-6 ký tự nữa.
         * Chỉ coi là coin nếu tìm được coin rõ ràng từ alias hoặc DB.
         */
        return $this->findCoinFromMessage($text) !== null;
    }

    private function getTopFaqMatches(string $expandedMessage): array
    {
        if (! Schema::hasTable('chatbot_faqs')) {
            return [];
        }

        $query = ChatbotFaq::query();

        if (Schema::hasColumn('chatbot_faqs', 'is_active')) {
            $query->where('is_active', true);
        }

        $faqs = $query->get();
        $matches = [];

        foreach ($faqs as $faq) {
            $score = $this->calculateScore($expandedMessage, $faq);

            if ($score > 0) {
                $matches[] = [
                    'faq' => $faq,
                    'score' => $score,
                ];
            }
        }

        usort($matches, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $matches;
    }

    private function calculateScore(string $expandedMessage, ChatbotFaq $faq): int
    {
        $score = 0;

        $question = $this->normalizeText($faq->question ?? '');
        $answer = $this->normalizeText($faq->answer ?? '');
        $category = $this->normalizeText($faq->category ?? '');

        if ($question !== '' && $expandedMessage === $question) {
            $score += 20;
        }

        if ($question !== '' && str_contains($expandedMessage, $question)) {
            $score += 12;
        }

        if ($question !== '' && str_contains($question, $expandedMessage) && mb_strlen($expandedMessage) >= 5) {
            $score += 8;
        }

        $messageTokens = $this->tokenize($expandedMessage);
        $questionTokens = $this->tokenize($question);
        $answerTokens = $this->tokenize($answer);

        foreach ($messageTokens as $token) {
            if (in_array($token, $questionTokens, true)) {
                $score += 3;
            }

            if (in_array($token, $answerTokens, true)) {
                $score += 1;
            }

            if ($category && str_contains($category, $token)) {
                $score += 2;
            }
        }

        $keywords = $faq->keywords ?? [];

        if (! is_array($keywords)) {
            $keywords = [];
        }

        foreach ($keywords as $keyword) {
            $normalizedKeyword = $this->normalizeText((string) $keyword);

            if ($normalizedKeyword === '') {
                continue;
            }

            if (str_contains($expandedMessage, $normalizedKeyword)) {
                $score += 8;
                continue;
            }

            $keywordTokens = $this->tokenize($normalizedKeyword);
            $matchedKeywordTokens = 0;

            foreach ($keywordTokens as $keywordToken) {
                if (in_array($keywordToken, $messageTokens, true)) {
                    $matchedKeywordTokens++;
                }
            }

            if (count($keywordTokens) > 0 && $matchedKeywordTokens === count($keywordTokens)) {
                $score += 5;
            } elseif ($matchedKeywordTokens > 0) {
                $score += $matchedKeywordTokens;
            }
        }

        similar_text($expandedMessage, $question, $questionSimilarity);

        if ($questionSimilarity >= 75) {
            $score += 6;
        } elseif ($questionSimilarity >= 55) {
            $score += 3;
        }

        return $score;
    }

    private function getRelatedContent(string $normalizedMessage): array
    {
        $tokens = $this->tokenize($normalizedMessage);
        $tokens = array_slice($tokens, 0, 6);

        if (count($tokens) === 0) {
            return [];
        }

        $links = [];

        if (Schema::hasTable('blog_posts')) {
            $blogPosts = BlogPost::query()
                ->when(Schema::hasColumn('blog_posts', 'status'), function ($query) {
                    $query->where('status', 'approved');
                })
                ->where(function ($query) use ($tokens) {
                    foreach ($tokens as $token) {
                        if (Schema::hasColumn('blog_posts', 'title')) {
                            $query->orWhere('title', 'like', '%' . $token . '%');
                        }

                        if (Schema::hasColumn('blog_posts', 'content')) {
                            $query->orWhere('content', 'like', '%' . $token . '%');
                        }
                    }
                })
                ->latest()
                ->take(3)
                ->get();

            foreach ($blogPosts as $post) {
                if (! empty($post->slug)) {
                    $links[] = [
                        'type' => 'Blog',
                        'title' => $post->title,
                        'url' => route('blog.show', $post->slug),
                    ];
                }
            }
        }

        if (Schema::hasTable('news')) {
            $newsItems = News::query()
                ->where(function ($query) use ($tokens) {
                    foreach ($tokens as $token) {
                        if (Schema::hasColumn('news', 'title')) {
                            $query->orWhere('title', 'like', '%' . $token . '%');
                        }

                        if (Schema::hasColumn('news', 'summary')) {
                            $query->orWhere('summary', 'like', '%' . $token . '%');
                        }

                        if (Schema::hasColumn('news', 'content')) {
                            $query->orWhere('content', 'like', '%' . $token . '%');
                        }

                        if (Schema::hasColumn('news', 'source')) {
                            $query->orWhere('source', 'like', '%' . $token . '%');
                        }
                    }
                })
                ->latest()
                ->take(3)
                ->get();

            foreach ($newsItems as $item) {
                if (! empty($item->slug)) {
                    $links[] = [
                        'type' => 'News',
                        'title' => $item->title,
                        'url' => route('news.show', $item->slug),
                    ];
                }
            }
        }

        return array_slice($links, 0, 5);
    }

    private function buildFallbackAnswer(string $normalizedMessage, string $intent): string
{
    return match ($intent) {
        'abuse' => 'Mình sẽ cố gắng hỗ trợ tốt hơn. Bạn có thể hỏi mình về crypto, giá coin, blog, tin tức, đăng ký làm tác giả, bình luận hoặc cách dùng website.',
        'out_of_scope' => 'Mình hiện chỉ hỗ trợ các nội dung liên quan đến crypto, tài chính cá nhân, blog, tin tức, tác giả, bình luận và cách dùng website. Câu hỏi này có vẻ nằm ngoài phạm vi hỗ trợ của mình.',
        'coin_price', 'coin_lookup' => 'Mình hiểu bạn đang hỏi về coin hoặc giá crypto, nhưng hiện hệ thống chưa có đủ dữ liệu để trả lời chính xác. Bạn có thể vào mục “Giá Crypto” để kiểm tra các coin đang được theo dõi, hoặc hỏi rõ hơn như “giá BTC”, “ETH là gì?”.',
        'author' => 'Bài viết của author sau khi gửi sẽ cần admin kiểm duyệt trước khi hiển thị công khai. Nếu bài của bạn chưa hiển thị, khả năng cao là bài đang ở trạng thái chờ duyệt hoặc đã bị từ chối. Bạn có thể vào mục “Bài của tôi” để xem trạng thái và lý do nếu có.',
        'admin' => 'Câu hỏi của bạn có vẻ liên quan đến quản trị. Admin có thể xem dashboard, duyệt tác giả, duyệt blog, quản lý tin tức và quản lý bình luận.',
        'comment' => 'Câu hỏi của bạn có vẻ liên quan đến bình luận. Người dùng cần đăng nhập để bình luận. Người tạo bình luận có thể xóa bình luận của mình, admin có thể xóa các bình luận vi phạm.',
        'risk' => 'Câu hỏi của bạn có vẻ liên quan đến rủi ro crypto. Hãy cẩn trọng với private key, seed phrase, cam kết lợi nhuận chắc chắn, airdrop lạ và các dự án thiếu minh bạch. Nội dung này chỉ mang tính tham khảo, không phải lời khuyên đầu tư.',
        'crypto_education' => 'Mình chưa có FAQ chính xác cho khái niệm này. Bạn có thể hỏi cụ thể hơn về Bitcoin, Ethereum, Stablecoin, DeFi, Blockchain, rủi ro đầu tư hoặc cách bảo mật ví crypto.',
        'website' => 'Mình chưa hiểu chính xác thao tác bạn muốn hỏi. Bạn có thể hỏi rõ hơn như “Làm sao để đọc blog?”, “Làm sao để xem tin tức?”, “Làm sao để bình luận?” hoặc “Làm sao để đăng ký làm tác giả?”.',
        'unknown' => 'Mình chưa chắc ý bạn đang hỏi là gì. Bạn có thể hỏi rõ hơn về crypto, giá coin, blog, tin tức, đăng ký làm tác giả, trạng thái bài viết, bình luận hoặc cách dùng website.',
        default => 'Mình chưa có câu trả lời chính xác. Bạn có thể hỏi theo các nhóm như crypto cơ bản, rủi ro đầu tư, giá coin, cách dùng website, đăng ký làm tác giả, duyệt bài viết hoặc bình luận.',
    };
}

    private function getSuggestions(?string $category): array
    {
        return match ($category) {
            'crypto_basic', 'crypto_education', 'coin_lookup', 'coin_price', 'crypto_coin' => [
                'Stablecoin là gì?',
                'DeFi là gì?',
                'Rủi ro khi đầu tư crypto là gì?',
            ],
            'risk' => [
                'Có nên chia sẻ private key không?',
                'Làm sao nhận biết scam crypto?',
                'Thông tin trên website có phải lời khuyên đầu tư không?',
            ],
            'website', 'content_search', 'content_related' => [
                'Làm sao để xem giá crypto?',
                'Làm sao để bình luận?',
                'Làm sao để đọc blog?',
            ],
            'author' => [
                'Làm sao để trở thành tác giả?',
                'Vì sao bài viết của tôi chưa hiển thị?',
                'Nếu bài viết bị từ chối thì làm gì?',
            ],
            'admin' => [
                'Admin có thể làm gì?',
                'Dashboard dùng để làm gì?',
                'Admin duyệt bài như thế nào?',
            ],
            'abuse', 'out_of_scope' => [
                'Bitcoin là gì?',
                'Làm sao để trở thành tác giả?',
                'Có nên chia sẻ private key không?',
            ],
            default => [
                'Bitcoin là gì?',
                'Làm sao để trở thành tác giả?',
                'Có nên chia sẻ private key không?',
            ],
        };
    }

    private function simpleTokens(string $text): array
    {
        $text = $this->normalizeText($text);

        $tokens = preg_split('/\s+/', $text);

        $tokens = array_filter($tokens, function ($token) {
            return $token !== '';
        });

        return array_values(array_unique($tokens));
    }

    private function hasExactToken(string $text, string $token): bool
    {
        $tokens = $this->simpleTokens($text);
        $token = $this->normalizeText($token);

        return in_array($token, $tokens, true);
    }

    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $this->normalizeText($needle))) {
                return true;
            }
        }

        return false;
    }

    private function tokenize(string $text): array
    {
        $stopwords = [
            'la',
            'gi',
            'nhu',
            'nao',
            'co',
            'khong',
            'toi',
            'minh',
            'ban',
            'lam',
            'sao',
            'de',
            'mot',
            'nhung',
            'cac',
            've',
            'cho',
            'trong',
            'nay',
            'kia',
            'thi',
            'ma',
            'neu',
            'duoc',
            'hay',
            'hoi',
            'can',
            'muon',
            'xin',
            'em',
            'anh',
            'chi',
            'cua',
            'voi',
        ];

        $tokens = preg_split('/\s+/', $text);

        $tokens = array_filter($tokens, function ($token) use ($stopwords) {
            return mb_strlen($token) >= 2 && ! in_array($token, $stopwords, true);
        });

        return array_values(array_unique($tokens));
    }

    private function expandMessageWithSynonyms(string $normalizedMessage): string
    {
        $synonymGroups = [
            ['bitcoin', 'btc'],
            ['ethereum', 'eth'],
            ['stablecoin', 'usdt', 'usdc', 'dai'],
            ['defi', 'lending', 'dex', 'yield', 'decentralized finance'],
            ['scam', 'lua dao', 'gia mao'],
            ['private key', 'seed phrase', 'mat khau vi', 'bao mat'],
            ['author', 'tac gia', 'nguoi viet', 'viet bai', 'dang bai'],
            ['pending', 'cho duyet', 'chua hien thi', 'chua public'],
            ['reject', 'rejected', 'tu choi', 'bi tu choi'],
            ['approve', 'approved', 'duyet', 'phe duyet', 'chap nhan'],
            ['comment', 'binh luan', 'phan hoi'],
            ['news', 'tin tuc', 'bai bao'],
            ['blog', 'bai viet', 'post'],
            ['dashboard', 'admin', 'quan tri'],
            ['price', 'gia', 'bang gia', 'crypto price'],
            ['category', 'chuyen muc', 'phan loai'],
        ];

        $expanded = $normalizedMessage;

        foreach ($synonymGroups as $group) {
            foreach ($group as $word) {
                $normalizedWord = $this->normalizeText($word);

                if ($normalizedWord !== '' && str_contains($normalizedMessage, $normalizedWord)) {
                    $expanded .= ' ' . implode(' ', array_map(fn ($item) => $this->normalizeText($item), $group));
                    break;
                }
            }
        }

        return trim(preg_replace('/\s+/', ' ', $expanded));
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function getValueFromRow(object $row, array $columns)
    {
        foreach ($columns as $column) {
            if (property_exists($row, $column)) {
                return $row->{$column};
            }
        }

        return null;
    }

    private function formatPrice($price): string
    {
        if (! is_numeric($price)) {
            return (string) $price;
        }

        $price = (float) $price;

        if ($price >= 1) {
            return number_format($price, 2);
        }

        return rtrim(rtrim(number_format($price, 8), '0'), '.');
    }

    private function formatLargeNumber($number): string
    {
        if (! is_numeric($number)) {
            return (string) $number;
        }

        $number = (float) $number;

        if ($number >= 1_000_000_000) {
            return number_format($number / 1_000_000_000, 2) . 'B';
        }

        if ($number >= 1_000_000) {
            return number_format($number / 1_000_000, 2) . 'M';
        }

        return number_format($number, 0);
    }

    private function formatDateTime($value): string
    {
        try {
            return Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function normalizeText(string $text): string
    {
        $text = Str::lower($text);

        $from = [
            'á','à','ả','ã','ạ','ă','ắ','ằ','ẳ','ẵ','ặ','â','ấ','ầ','ẩ','ẫ','ậ',
            'é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ',
            'í','ì','ỉ','ĩ','ị',
            'ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ',
            'ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự',
            'ý','ỳ','ỷ','ỹ','ỵ',
            'đ',
        ];

        $to = [
            'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
            'e','e','e','e','e','e','e','e','e','e','e',
            'i','i','i','i','i',
            'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
            'u','u','u','u','u','u','u','u','u','u','u',
            'y','y','y','y','y',
            'd',
        ];

        $text = str_replace($from, $to, $text);
        $text = preg_replace('/[^a-z0-9\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}