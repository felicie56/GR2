<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFeedback;
use App\Models\ChatbotMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotFeedbackController extends Controller
{
    public function store(
        Request $request,
        ChatbotMessage $message
    ): JsonResponse {
        $validated = $request->validate([
            'rating' => [
                'required',
                'in:' . ChatbotFeedback::RATING_HELPFUL
                    . ',' . ChatbotFeedback::RATING_NOT_HELPFUL,
            ],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($message->isAssistant(), 422);

        $session = $message->session;
        $guestToken = $request->cookie((string) config(
            'chatbot.conversation.guest_cookie_name',
            'chatbot_guest_token'
        ));

        abort_unless(
            $session && $session->isOwnedBy($request->user(), $guestToken),
            403
        );

        $identity = $request->user()
            ? [
                'message_id' => $message->id,
                'user_id' => $request->user()->id,
            ]
            : [
                'message_id' => $message->id,
                'guest_token_hash' => hash('sha256', (string) $guestToken),
            ];

        $feedback = ChatbotFeedback::query()->updateOrCreate(
            $identity,
            [
                'rating' => $validated['rating'],
                'reason' => $validated['reason'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'feedback_id' => $feedback->id,
            'rating' => $feedback->rating,
        ]);
    }
}