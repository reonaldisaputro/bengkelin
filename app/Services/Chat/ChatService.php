<?php

namespace App\Services\Chat;

use App\Models\User;
use App\Services\Chat\Handlers\BookingFlowHandler;
use App\Services\Chat\Handlers\BookingHistoryHandler;
use App\Services\Chat\Handlers\FaqHandler;
use App\Services\Chat\Handlers\MenuHandler;
use App\Services\Chat\Handlers\NearbyBengkelHandler;
use App\Services\Chat\Handlers\ProductSearchHandler;
use App\Services\Chat\Handlers\RatingHandler;
use App\Services\Chat\Handlers\StatusHandler;

class ChatService
{
    private ContextManager $contextManager;
    private IntentMatcher $intentMatcher;

    // Handlers
    private MenuHandler $menuHandler;
    private FaqHandler $faqHandler;
    private StatusHandler $statusHandler;
    private RatingHandler $ratingHandler;
    private NearbyBengkelHandler $nearbyHandler;
    private ProductSearchHandler $productHandler;
    private BookingHistoryHandler $bookingHistoryHandler;
    private BookingFlowHandler $bookingFlowHandler;

    public function __construct()
    {
        $this->contextManager = new ContextManager();
        $this->intentMatcher = new IntentMatcher();

        // Initialize handlers
        $this->menuHandler = new MenuHandler();
        $this->faqHandler = new FaqHandler($this->intentMatcher);
        $this->statusHandler = new StatusHandler();
        $this->ratingHandler = new RatingHandler();
        $this->nearbyHandler = new NearbyBengkelHandler();
        $this->productHandler = new ProductSearchHandler($this->contextManager);
        $this->bookingHistoryHandler = new BookingHistoryHandler();
        $this->bookingFlowHandler = new BookingFlowHandler($this->contextManager);
    }

    /**
     * Main entry point - handle incoming message
     */
    public function handleMessage(
        User $user,
        string $message,
        ?string $payload = null,
        $latitude = null,
        $longitude = null,
        $radius = 10
    ): array {
        $message = trim($message);
        $payload = $payload ? trim($payload) : null;

        // Check if user wants to cancel current flow
        if ($this->contextManager->isInFlow($user->id) && $this->intentMatcher->isCancelIntent($message, $payload)) {
            $this->contextManager->clearFlow($user->id);
            return $this->addFlowCancelledMessage($this->menuHandler->handle($user));
        }

        // If user is in an active flow, route to that flow handler
        $currentFlow = $this->contextManager->getCurrentFlow($user->id);
        if ($currentFlow) {
            return $this->handleActiveFlow($user, $message, $payload, $currentFlow);
        }

        // Match intent from message/payload
        $intentResult = $this->intentMatcher->match($message, $payload);
        $intent = $intentResult['intent'];
        $matches = $intentResult['matches'];

        // Route to appropriate handler based on intent
        return $this->routeToHandler($user, $message, $payload, $intent, $matches, $latitude, $longitude, $radius);
    }

    /**
     * Handle message when user is in an active flow
     */
    private function handleActiveFlow(User $user, string $message, ?string $payload, string $flow): array
    {
        return match ($flow) {
            'booking' => $this->bookingFlowHandler->handle($user, $message, $payload),
            default => $this->menuHandler->handle($user),
        };
    }

    /**
     * Route to the appropriate handler based on detected intent
     */
    private function routeToHandler(
        User $user,
        string $message,
        ?string $payload,
        ?string $intent,
        array $matches,
        $latitude = null,
        $longitude = null,
        $radius = 10
    ): array {
        return match ($intent) {
            // Menu
            'menu' => $this->menuHandler->handle($user),

            // FAQ
            'faq' => $this->faqHandler->handle($user),
            'faq_answer' => $this->handleFaqAnswer($user, $matches),
            'faq_specific' => $this->faqHandler->handleSearch($user, $message),

            // Booking Flow
            'booking', 'booking_prompt' => $this->startBookingFlow($user),
            'booking_select_bengkel' => $this->handleBookingPayload($user, $message, $payload, $matches),

            // Booking History
            'booking_history', 'booking_history_prompt' => $this->bookingHistoryHandler->handle($user),
            'cancel_booking' => $this->handleCancelBooking($user, $matches),

            // Status
            'status', 'status_prompt' => $this->statusHandler->handle($user),
            'status_specific' => $this->handleStatusSpecific($user, $message),

            // Rating
            'rating', 'rate_list' => $this->ratingHandler->handle($user),
            'rate_prompt' => $this->handleRatePrompt($user, $matches),
            'rate_submit' => $this->handleRateSubmit($user, $message),

            // Product Search
            'product_search', 'product_search_prompt' => $this->productHandler->handle($user),
            'add_to_cart' => $this->handleAddToCart($user, $matches),

            // Nearby Bengkel
            'nearby', 'nearby_prompt' => $this->handleNearby($user, $message, $latitude, $longitude, $radius),
            'nearby_list' => $this->nearbyHandler->handleList($user),
            'nearby_with_coords' => $this->handleNearbyWithCoords($user, $message),

            // Default / Fallback
            default => $this->handleFallback($user, $message, $payload, $latitude, $longitude, $radius),
        };
    }

    /**
     * Start booking flow
     */
    private function startBookingFlow(User $user): array
    {
        return $this->bookingFlowHandler->handleInit($user);
    }

    /**
     * Handle booking-related payloads when in booking flow
     */
    private function handleBookingPayload(User $user, string $message, ?string $payload, array $matches): array
    {
        // If not in booking flow, start it first
        if (!$this->contextManager->isInFlow($user->id)) {
            $this->contextManager->updateFlow($user->id, 'booking', 'select_bengkel');
        }
        return $this->bookingFlowHandler->handle($user, $message, $payload, $matches);
    }

    /**
     * Handle FAQ answer payload
     */
    private function handleFaqAnswer(User $user, array $matches): array
    {
        $faqKey = $matches[1] ?? null;
        if ($faqKey) {
            return $this->faqHandler->handleAnswer($user, $faqKey);
        }
        return $this->faqHandler->handle($user);
    }

    /**
     * Handle specific transaction status
     */
    private function handleStatusSpecific(User $user, string $message): array
    {
        $code = $this->intentMatcher->extractTransactionCode($message);
        if ($code) {
            return $this->statusHandler->handleSpecific($user, $code);
        }
        return $this->statusHandler->handle($user);
    }

    /**
     * Handle rate prompt for specific item
     */
    private function handleRatePrompt(User $user, array $matches): array
    {
        $detailId = (int) ($matches[1] ?? 0);
        if ($detailId) {
            return $this->ratingHandler->handlePrompt($user, $detailId);
        }
        return $this->ratingHandler->handle($user);
    }

    /**
     * Handle rate submission
     */
    private function handleRateSubmit(User $user, string $message): array
    {
        $parsed = $this->ratingHandler->parseRateMessage($message);
        if ($parsed) {
            return $this->ratingHandler->handleSubmit(
                $user,
                $parsed['detail_id'],
                $parsed['stars'],
                $parsed['comment']
            );
        }
        return $this->ratingHandler->handle($user);
    }

    /**
     * Handle add to cart
     */
    private function handleAddToCart(User $user, array $matches): array
    {
        $productId = (int) ($matches[1] ?? 0);
        if ($productId) {
            return $this->productHandler->handleAddToCart($user, $productId);
        }
        return $this->productHandler->handle($user);
    }

    /**
     * Handle nearby bengkel - with optional lat/long params or from message
     */
    private function handleNearby(User $user, string $message, $latitude = null, $longitude = null, $radius = 10): array
    {
        // If lat/long provided as parameters, use them
        if ($latitude !== null && $longitude !== null) {
            return $this->nearbyHandler->handleWithCoords($user, (float) $latitude, (float) $longitude, (float) $radius);
        }

        // Otherwise, try to parse from message
        $coords = $this->nearbyHandler->parseCoordinates($message);
        if ($coords) {
            return $this->nearbyHandler->handleWithCoords($user, $coords['lat'], $coords['lng'], $radius);
        }

        // Show instructions
        return $this->nearbyHandler->handle($user);
    }

    /**
     * Handle nearby with coordinates from message
     */
    private function handleNearbyWithCoords(User $user, string $message): array
    {
        $coords = $this->nearbyHandler->parseCoordinates($message);
        if ($coords) {
            return $this->nearbyHandler->handleWithCoords($user, $coords['lat'], $coords['lng']);
        }
        return $this->nearbyHandler->handle($user);
    }

    /**
     * Handle cancel booking
     */
    private function handleCancelBooking(User $user, array $matches): array
    {
        $bookingId = (int) ($matches[1] ?? 0);
        if ($bookingId) {
            return $this->bookingHistoryHandler->handleCancel($user, $bookingId);
        }
        return $this->bookingHistoryHandler->handle($user);
    }

    /**
     * Handle fallback - try to understand message or show menu
     */
    private function handleFallback(User $user, string $message, ?string $payload, $latitude = null, $longitude = null, $radius = 10): array
    {
        // If lat/long provided, treat as nearby request
        if ($latitude !== null && $longitude !== null) {
            return $this->nearbyHandler->handleWithCoords($user, (float) $latitude, (float) $longitude, (float) $radius);
        }

        // Try product search if message looks like a search query
        $keyword = $this->productHandler->extractKeyword($message);
        if ($keyword) {
            return $this->productHandler->handleSearch($user, $keyword);
        }

        // Try FAQ search if message is a question
        if (str_contains($message, '?') || preg_match('/^(cara|bagaimana|gimana|apa|kenapa)/i', $message)) {
            $faqKey = $this->intentMatcher->matchFaqQuestion($message);
            if ($faqKey) {
                return $this->faqHandler->handleAnswer($user, $faqKey);
            }
        }

        // Try nearby if message contains coordinates
        $coords = $this->nearbyHandler->parseCoordinates($message);
        if ($coords) {
            return $this->nearbyHandler->handleWithCoords($user, $coords['lat'], $coords['lng'], $radius);
        }

        // Show fallback with menu
        return ResponseBuilder::make()
            ->text("Maaf, saya kurang paham maksud Anda.")
            ->text("Silakan pilih menu di bawah atau ketik pertanyaan dengan lebih jelas:")
            ->quickReplies([
                ['title' => 'Menu', 'payload' => 'menu'],
                ['title' => 'Status Pesanan', 'payload' => 'status_prompt'],
                ['title' => 'Booking Bengkel', 'payload' => 'booking_prompt'],
                ['title' => 'Bengkel Terdekat', 'payload' => 'nearby_prompt'],
                ['title' => 'FAQ', 'payload' => 'faq_prompt'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Add flow cancelled message to response
     */
    private function addFlowCancelledMessage(array $response): array
    {
        $cancelMessage = ['type' => 'text', 'text' => config('chat.messages.flow_cancelled', 'Dibatalkan. Kembali ke menu utama.')];
        array_unshift($response['messages'], $cancelMessage);
        return $response;
    }

    /**
     * Get context manager (for external use if needed)
     */
    public function getContextManager(): ContextManager
    {
        return $this->contextManager;
    }

    /**
     * Get intent matcher (for external use if needed)
     */
    public function getIntentMatcher(): IntentMatcher
    {
        return $this->intentMatcher;
    }
}
