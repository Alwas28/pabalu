<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->role === 'admin', 403);

        return view('chat.index');
    }

    public function conversations(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user->role === 'admin', 403);

        $rows = ChatMessage::selectRaw('owner_id, MAX(id) as last_id')
            ->groupBy('owner_id')
            ->orderByDesc('last_id')
            ->get();

        $ownerIds = $rows->pluck('owner_id');
        $owners   = User::whereIn('id', $ownerIds)->get()->keyBy('id');
        $lastMsgs = ChatMessage::whereIn('id', $rows->pluck('last_id'))->get()->keyBy('owner_id');

        $unreadPerOwner = ChatMessage::whereIn('owner_id', $ownerIds)
            ->whereNull('read_at')
            ->whereColumn('sender_id', 'owner_id')
            ->selectRaw('owner_id, COUNT(*) as cnt')
            ->groupBy('owner_id')
            ->pluck('cnt', 'owner_id');

        $data = $rows->map(function ($row) use ($owners, $lastMsgs, $unreadPerOwner) {
            $owner   = $owners[$row->owner_id] ?? null;
            $lastMsg = $lastMsgs[$row->owner_id] ?? null;
            return [
                'owner_id'   => $row->owner_id,
                'owner_name' => $owner?->name ?? '—',
                'last_msg'   => $lastMsg ? mb_strimwidth($lastMsg->message, 0, 60, '…') : '',
                'last_at'    => $lastMsg?->created_at->diffForHumans() ?? '',
                'unread'     => $unreadPerOwner[$row->owner_id] ?? 0,
            ];
        });

        return response()->json($data);
    }

    public function messages(int $ownerId, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->role === 'owner') {
            abort_unless($user->id === $ownerId, 403);
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $since = (int) $request->query('since', 0);

        $msgs = ChatMessage::where('owner_id', $ownerId)
            ->where('id', '>', $since)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'sender_id'  => $m->sender_id,
                'message'    => $m->message,
                'from_admin' => $m->sender_id !== $ownerId,
                'time'       => $m->created_at->format('H:i'),
                'date'       => $m->created_at->format('d M Y'),
            ]);

        // mark incoming messages as read
        ChatMessage::where('owner_id', $ownerId)
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update(['read_at' => now()]);

        return response()->json($msgs);
    }

    public function send(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->role === 'owner') {
            $ownerId = $user->id;
        } elseif ($user->role === 'admin') {
            $request->validate(['owner_id' => ['required', 'exists:users,id']]);
            $ownerId = (int) $request->input('owner_id');
        } else {
            abort(403);
        }

        $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $msg = ChatMessage::create([
            'owner_id'  => $ownerId,
            'sender_id' => $user->id,
            'message'   => $request->input('message'),
        ]);

        return response()->json([
            'id'         => $msg->id,
            'sender_id'  => $msg->sender_id,
            'message'    => $msg->message,
            'from_admin' => $msg->sender_id !== $ownerId,
            'time'       => $msg->created_at->format('H:i'),
            'date'       => $msg->created_at->format('d M Y'),
        ], 201);
    }

    public function unreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->role === 'owner') {
            $count = ChatMessage::where('owner_id', $user->id)
                ->whereNull('read_at')
                ->where('sender_id', '!=', $user->id)
                ->count();
        } elseif ($user->role === 'admin') {
            $count = ChatMessage::whereNull('read_at')
                ->whereColumn('sender_id', 'owner_id')
                ->count();
        } else {
            $count = 0;
        }

        return response()->json(['count' => $count]);
    }
}
