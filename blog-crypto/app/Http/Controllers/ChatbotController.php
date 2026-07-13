<?php

namespace App\Http\Controllers;

use App\Exceptions\OpenAiApiException;
use App\Models\ChatbotMessage;
use App\Services\Chatbot\ChatbotTurnService;
use App\Services\Chatbot\ConversationSessionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(
        private readonly ConversationSessionService $sessions,
        private readonly ChatbotTurnService $turns
    ) {
    }

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'min:1',
                'max:' . (int) config(
                    'chatbot.conversation.max_message_characters',
                    2000
                ),
            ],
            'session_uuid' => ['nullable', 'uuid'],
        ]);

        $guestToken = $request->cookie($this->guestCookieName());

        try {
            $resolved = $this->sessions->resolve(
                user: $request->user(),
                sessionUuid: $validated['session_uuid'] ?? null,
                guestToken: $guestToken
            );

            $result = $this->turns->ask(
                session: $resolved['session'],
                message: (string) $validated['message'],
                user: $request->user()
            );

            $response = response()->json([
                'success' => true,
                'session_uuid' => $result['session']->uuid,
                'message_id' => $result['assistant_message']->id,
                'answer' => $result['answer'],
                'sources' => $result['sources'],
                'related_links' => $result['sources'],
                'suggestions' => $result['suggestions'],
                'meta' => [
                    'model' => $result['model'],
                    'latency_ms' => $result['latency_ms'],
                ],
            ]);

            return $this->withGuestCookie(
                $response,
                $request,
                $resolved
            );
        } catch (AuthorizationException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 403);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (OpenAiApiException $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Dịch vụ AI hiện chưa thể phản hồi. Vui lòng thử lại sau.',
                'error_code' => $exception->errorCode,
            ], in_array($exception->statusCode, [429, 503], true) ? 503 : 502);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Chatbot gặp lỗi khi xử lý câu hỏi. Vui lòng thử lại.',
            ], 500);
        }
    }

    public function startSession(Request $request): JsonResponse
    {
        $guestToken = $request->cookie($this->guestCookieName());

        $resolved = $this->sessions->resolve(
            user: $request->user(),
            sessionUuid: null,
            guestToken: $guestToken
        );

        $response = response()->json([
            'success' => true,
            'session_uuid' => $resolved['session']->uuid,
            'messages' => [],
        ]);

        return $this->withGuestCookie($response, $request, $resolved);
    }

    public function history(Request $request, string $uuid): JsonResponse
    {
        try {
            $resolved = $this->sessions->resolve(
                user: $request->user(),
                sessionUuid: $uuid,
                guestToken: $request->cookie($this->guestCookieName())
            );

            $messages = $resolved['session']->messages()
                ->where('status', ChatbotMessage::STATUS_COMPLETED)
                ->whereIn('role', [
                    ChatbotMessage::ROLE_USER,
                    ChatbotMessage::ROLE_ASSISTANT,
                ])
                ->orderBy('id')
                ->limit(100)
                ->get()
                ->map(fn (ChatbotMessage $message) => [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'sources' => $message->sources ?? [],
                    'created_at' => $message->created_at?->toIso8601String(),
                ]);

            return response()->json([
                'success' => true,
                'session_uuid' => $resolved['session']->uuid,
                'title' => $resolved['session']->title,
                'messages' => $messages,
            ]);
        } catch (AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem phiên trò chuyện này.',
            ], 403);
        }
    }

    /**
     * @param array{
     *     session: mixed,
     *     guest_token: ?string,
     *     should_set_guest_cookie: bool
     * } $resolved
     */
    private function withGuestCookie(
        JsonResponse $response,
        Request $request,
        array $resolved
    ): JsonResponse {
        if (
            ! $resolved['should_set_guest_cookie']
            || ! $resolved['guest_token']
        ) {
            return $response;
        }

        $minutes = max(1, (int) config(
            'chatbot.conversation.guest_cookie_days',
            30
        )) * 24 * 60;

        $response->withCookie(Cookie::make(
            $this->guestCookieName(),
            $resolved['guest_token'],
            $minutes,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax'
        ));

        return $response;
    }

    private function guestCookieName(): string
    {
        return (string) config(
            'chatbot.conversation.guest_cookie_name',
            'chatbot_guest_token'
        );
    }
}