<?php

namespace App\Services\Chat;

class IntentMatcher
{
    private array $synonyms;
    private int $fuzzyThreshold;

    /**
     * Intent definitions with patterns and keywords
     */
    private array $intents = [
        'menu' => [
            'patterns' => [],
            'keywords' => ['menu', 'home', 'utama', 'mulai', 'start'],
            'priority' => 1,
        ],
        'faq' => [
            'patterns' => ['/^faq\b/i', '/^bantuan\b/i', '/^help\b/i'],
            'keywords' => ['faq', 'bantuan', 'help', 'pertanyaan'],
            'priority' => 3,
        ],
        'faq_specific' => [
            'patterns' => ['/^(cara|bagaimana|gimana)\s+/i', '/\?(cara|bagaimana)/i'],
            'keywords' => [],
            'priority' => 3,
        ],
        'booking' => [
            'patterns' => ['/^booking\b/i', '/^pesan\s+bengkel/i', '/^reservasi\b/i', '/^servis\b/i'],
            'keywords' => ['booking', 'reservasi'],
            'priority' => 2,
        ],
        'product_search' => [
            'patterns' => [
                '/^cari\s+(.+)/i',
                '/^produk\s+(.+)/i',
                '/^beli\s+(.+)/i',
                '/^spare\s*part\s+(.+)/i',
            ],
            'keywords' => [],
            'priority' => 2,
        ],
        'booking_history' => [
            'patterns' => [
                '/^booking\s+saya/i',
                '/^riwayat\s+booking/i',
                '/^jadwal\s+saya/i',
                '/^daftar\s+booking/i',
            ],
            'keywords' => [],
            'priority' => 2,
        ],
        'status_specific' => [
            'patterns' => ['/\b(TRANS-\w+)\b/i'],
            'keywords' => [],
            'priority' => 1,
        ],
        'status' => [
            'patterns' => ['/^status\b/i', '/^pesanan\b/i', '/^transaksi\b/i', '/^order\b/i'],
            'keywords' => ['status', 'pesanan', 'transaksi'],
            'priority' => 2,
        ],
        'rating' => [
            'patterns' => ['/^rating\b/i', '/^ulas\b/i', '/^review\b/i', '/^ulasan\b/i'],
            'keywords' => ['rating', 'ulasan', 'review'],
            'priority' => 2,
        ],
        'rate_submit' => [
            'patterns' => ['/^rate\s+(\d+)\s+([1-5])(?:\s+"(.*)")?$/i'],
            'keywords' => [],
            'priority' => 1,
        ],
        'nearby' => [
            'patterns' => ['/^bengkel\s+terdekat/i', '/^nearby\b/i', '/^lokasi\s+bengkel/i'],
            'keywords' => ['terdekat', 'nearby'],
            'priority' => 2,
        ],
        'nearby_with_coords' => [
            'patterns' => ['/^bengkel\s+terdekat\s+([\-0-9\.]+),\s*([\-0-9\.]+)/i'],
            'keywords' => [],
            'priority' => 1,
        ],
        'cancel' => [
            'patterns' => ['/^batal\b/i', '/^cancel\b/i', '/^keluar\b/i'],
            'keywords' => ['batal', 'cancel', 'batalkan'],
            'priority' => 1,
        ],
    ];

    public function __construct()
    {
        $this->synonyms = config('chat.synonyms', []);
        $this->fuzzyThreshold = config('chat.intent.fuzzy_threshold', 70);
    }

    /**
     * Match message to an intent
     *
     * @return array{intent: string|null, matches: array, confidence: int}
     */
    public function match(string $message, ?string $payload = null): array
    {
        $message = trim($message);
        $messageLower = mb_strtolower($message);

        // Check payload-based intents first
        if ($payload) {
            $payloadIntent = $this->matchPayload($payload);
            if ($payloadIntent) {
                return [
                    'intent' => $payloadIntent['intent'],
                    'matches' => $payloadIntent['matches'] ?? [],
                    'confidence' => 100,
                    'payload' => $payload,
                ];
            }
        }

        // Empty message = menu
        if ($message === '' && !$payload) {
            return ['intent' => 'menu', 'matches' => [], 'confidence' => 100, 'payload' => null];
        }

        // Try pattern matching first (highest priority)
        foreach ($this->intents as $intentName => $config) {
            foreach ($config['patterns'] as $pattern) {
                if (preg_match($pattern, $message, $matches)) {
                    return [
                        'intent' => $intentName,
                        'matches' => $matches,
                        'confidence' => 100,
                        'payload' => null,
                    ];
                }
            }
        }

        // Try keyword matching
        $keywordMatch = $this->matchKeywords($messageLower);
        if ($keywordMatch) {
            return $keywordMatch;
        }

        // Try fuzzy matching
        $fuzzyMatch = $this->fuzzyMatch($messageLower);
        if ($fuzzyMatch) {
            return $fuzzyMatch;
        }

        // No match found
        return ['intent' => null, 'matches' => [], 'confidence' => 0, 'payload' => null];
    }

    /**
     * Match payload to intent
     */
    private function matchPayload(string $payload): ?array
    {
        $payloadMappings = [
            'menu' => 'menu',
            'status_prompt' => 'status',
            'rate_list' => 'rating',
            'nearby_prompt' => 'nearby',
            'faq_prompt' => 'faq',
            'booking_prompt' => 'booking',
            'product_search_prompt' => 'product_search',
            'booking_history_prompt' => 'booking_history',
        ];

        // Direct payload mapping
        if (isset($payloadMappings[$payload])) {
            return ['intent' => $payloadMappings[$payload], 'matches' => []];
        }

        // Dynamic payload patterns
        $dynamicPatterns = [
            '/^select_bengkel_(\d+)$/' => 'booking_select_bengkel',
            '/^select_date_(.+)$/' => 'booking_select_date',
            '/^select_time_(.+)$/' => 'booking_select_time',
            '/^select_brand_(.+)$/' => 'booking_select_brand',
            '/^select_year_(\d+)$/' => 'booking_select_year',
            '/^select_transmisi_(.+)$/' => 'booking_select_transmisi',
            '/^confirm_booking$/' => 'booking_confirm',
            '/^cancel_booking_(\d+)$/' => 'cancel_booking',
            '/^rate_prompt_(\d+)$/' => 'rate_prompt',
            '/^add_to_cart_(\d+)$/' => 'add_to_cart',
            '/^faq_(.+)$/' => 'faq_answer',
            '/^nearby_search$/' => 'nearby',
            '/^nearby_list$/' => 'nearby_list',
        ];

        foreach ($dynamicPatterns as $pattern => $intent) {
            if (preg_match($pattern, $payload, $matches)) {
                return ['intent' => $intent, 'matches' => $matches];
            }
        }

        return null;
    }

    /**
     * Match keywords in message
     */
    private function matchKeywords(string $messageLower): ?array
    {
        $words = preg_split('/\s+/', $messageLower);
        $matchedIntents = [];

        foreach ($this->intents as $intentName => $config) {
            if (empty($config['keywords'])) continue;

            foreach ($config['keywords'] as $keyword) {
                // Check direct keyword match
                if (in_array($keyword, $words) || str_contains($messageLower, $keyword)) {
                    $matchedIntents[$intentName] = [
                        'priority' => $config['priority'],
                        'confidence' => 90,
                    ];
                    break;
                }

                // Check synonym match
                foreach ($this->synonyms as $canonical => $synonymList) {
                    if ($canonical === $keyword || in_array($keyword, $synonymList)) {
                        foreach ($synonymList as $synonym) {
                            if (in_array($synonym, $words) || str_contains($messageLower, $synonym)) {
                                $matchedIntents[$intentName] = [
                                    'priority' => $config['priority'],
                                    'confidence' => 85,
                                ];
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        if (empty($matchedIntents)) {
            return null;
        }

        // Sort by priority (lower is better) and confidence (higher is better)
        uasort($matchedIntents, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] - $b['priority'];
            }
            return $b['confidence'] - $a['confidence'];
        });

        $bestIntent = array_key_first($matchedIntents);
        return [
            'intent' => $bestIntent,
            'matches' => [],
            'confidence' => $matchedIntents[$bestIntent]['confidence'],
            'payload' => null,
        ];
    }

    /**
     * Fuzzy match using Levenshtein distance
     */
    private function fuzzyMatch(string $messageLower): ?array
    {
        $words = preg_split('/\s+/', $messageLower);
        $bestMatch = null;
        $bestSimilarity = 0;

        foreach ($this->intents as $intentName => $config) {
            foreach ($config['keywords'] as $keyword) {
                foreach ($words as $word) {
                    if (strlen($word) < 3) continue;

                    similar_text($word, $keyword, $percent);
                    if ($percent >= $this->fuzzyThreshold && $percent > $bestSimilarity) {
                        $bestSimilarity = $percent;
                        $bestMatch = $intentName;
                    }
                }
            }
        }

        if ($bestMatch) {
            return [
                'intent' => $bestMatch,
                'matches' => [],
                'confidence' => (int) $bestSimilarity,
                'payload' => null,
            ];
        }

        return null;
    }

    /**
     * Extract search keyword from product search intent
     */
    public function extractSearchKeyword(string $message): ?string
    {
        $patterns = [
            '/^cari\s+(.+)/i',
            '/^produk\s+(.+)/i',
            '/^beli\s+(.+)/i',
            '/^spare\s*part\s+(.+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($message), $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract transaction code from message
     */
    public function extractTransactionCode(string $message): ?string
    {
        if (preg_match('/\b(TRANS-\w+)\b/i', $message, $matches)) {
            return strtoupper($matches[1]);
        }
        return null;
    }

    /**
     * Extract coordinates from message
     */
    public function extractCoordinates(string $message): ?array
    {
        if (preg_match('/(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/', $message, $matches)) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }
        return null;
    }

    /**
     * Check if message is a cancel intent
     */
    public function isCancelIntent(string $message, ?string $payload = null): bool
    {
        if ($payload === 'menu' || $payload === 'cancel') {
            return true;
        }

        $cancelKeywords = ['batal', 'cancel', 'batalkan', 'keluar', 'menu'];
        $messageLower = mb_strtolower(trim($message));

        foreach ($cancelKeywords as $keyword) {
            if ($messageLower === $keyword || str_starts_with($messageLower, $keyword . ' ')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match FAQ-specific question
     */
    public function matchFaqQuestion(string $message): ?string
    {
        $faqs = config('chat.faqs', []);
        $messageLower = mb_strtolower(trim($message));

        foreach ($faqs as $key => $faq) {
            foreach ($faq['keywords'] as $keyword) {
                if (str_contains($messageLower, mb_strtolower($keyword))) {
                    return $key;
                }
            }
        }

        // Fuzzy match for FAQ questions
        foreach ($faqs as $key => $faq) {
            foreach ($faq['keywords'] as $keyword) {
                similar_text($messageLower, mb_strtolower($keyword), $percent);
                if ($percent >= 70) {
                    return $key;
                }
            }
        }

        return null;
    }
}
