<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatApiController extends Controller
{
    private ChatService $chatService;

    public function __construct()
    {
        $this->chatService = new ChatService();
    }

    /**
     * Handle incoming chat message
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return ResponseFormatter::error(null, 'Unauthorized', 401);
        }

        $message = trim((string) $request->input('message', ''));
        $payload = trim((string) $request->input('payload', ''));

        // Handle empty payload
        $payload = $payload === '' ? null : $payload;

        // Optional location parameters for nearby bengkel
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $radius = $request->input('radius', 10); // Default 10km

        try {
            $response = $this->chatService->handleMessage(
                $user,
                $message,
                $payload,
                $latitude,
                $longitude,
                $radius
            );
            return ResponseFormatter::success($response, 'ok');
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Chatbot error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'message' => $message,
                'payload' => $payload,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'trace' => $e->getTraceAsString(),
            ]);

            return ResponseFormatter::error(
                ['error' => $e->getMessage()],
                'Terjadi kesalahan pada chatbot',
                500
            );
        }
    }
}
