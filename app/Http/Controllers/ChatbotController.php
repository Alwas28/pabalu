<?php

namespace App\Http\Controllers;

use App\Models\ChatbotMessage;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Widget Asisten Pabalu — bisa dipakai tamu maupun user login (route di luar grup auth).
// User login: riwayat disimpan di tabel chatbot_messages (bisa dibaca lagi kapan saja).
// Tamu: riwayat hanya di session, hilang saat session berakhir.
class ChatbotController extends Controller
{
    // Jumlah pesan yang ditampilkan ulang di widget saat dibuka.
    private const HISTORY_DISPLAY_LIMIT = 50;

    public function send(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $user    = Auth::user();
        $message = trim($validated['message']);

        if ($user) {
            $history = $this->userMessages($user->id, ChatbotService::MAX_HISTORY_MESSAGES);
            // Konteks untuk API harus diawali pesan user.
            while ($history && $history[0]['role'] !== 'user') {
                array_shift($history);
            }

            $reply = $chatbot->reply($user, $history, $message);

            ChatbotMessage::create(['user_id' => $user->id, 'role' => 'user', 'content' => $message]);
            ChatbotMessage::create(['user_id' => $user->id, 'role' => 'assistant', 'content' => $reply]);
        } else {
            $history = $request->session()->get('chatbot.history.guest', []);

            $reply = $chatbot->reply(null, $history, $message);

            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = ['role' => 'assistant', 'content' => $reply];
            $request->session()->put('chatbot.history.guest', array_slice($history, -ChatbotService::MAX_HISTORY_MESSAGES));
        }

        return response()->json(['reply' => $reply]);
    }

    public function history(): JsonResponse
    {
        $messages = Auth::check() ? $this->userMessages(Auth::id(), self::HISTORY_DISPLAY_LIMIT) : [];

        return response()->json(['messages' => $messages]);
    }

    public function reset(Request $request): JsonResponse
    {
        if (Auth::check()) {
            ChatbotMessage::where('user_id', Auth::id())->delete();
        } else {
            $request->session()->forget('chatbot.history.guest');
        }

        return response()->json(['message' => 'Percakapan dihapus.']);
    }

    /**
     * N pesan terakhir milik user, urut dari yang terlama.
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function userMessages(int $userId, int $limit): array
    {
        return ChatbotMessage::where('user_id', $userId)
            ->latest('id')
            ->limit($limit)
            ->get(['role', 'content'])
            ->reverse()
            ->map(fn (ChatbotMessage $m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();
    }
}
