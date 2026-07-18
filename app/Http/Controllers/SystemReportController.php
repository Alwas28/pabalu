<?php

namespace App\Http\Controllers;

use App\Models\SystemReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SystemReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->role === 'owner', 403);

        $validated = $request->validate([
            'subject'   => ['required', 'string', 'max:150'],
            'message'   => ['required', 'string', 'max:2000'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
        ]);

        SystemReport::create([
            'user_id'   => $user->id,
            'outlet_id' => $validated['outlet_id'] ?? null,
            'subject'   => $validated['subject'],
            'message'   => $validated['message'],
        ]);

        return response()->json(['message' => 'Laporan berhasil dikirim.']);
    }

    public function mine(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->role === 'owner', 403);

        $reports = SystemReport::where('user_id', $user->id)
            ->with('outlet:id,name')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'subject'     => $r->subject,
                'message'     => $r->message,
                'status'      => $r->status,
                'admin_reply' => $r->admin_reply,
                'outlet'      => $r->outlet?->name,
                'created_at'  => $r->created_at->diffForHumans(),
            ]);

        return response()->json($reports);
    }

    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->role === 'admin', 403);

        $reports     = SystemReport::with(['user', 'outlet:id,name'])->latest()->paginate(20);
        $unreadCount = SystemReport::where('status', 'unread')->count();

        return view('system-reports.index', compact('reports', 'unreadCount'));
    }

    public function markRead(SystemReport $report): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->role === 'admin', 403);

        $report->update(['status' => 'read', 'read_at' => now()]);

        return back()->with('success', 'Laporan ditandai sudah dibaca.');
    }

    public function reply(Request $request, SystemReport $report): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user->role === 'admin', 403);

        $validated = $request->validate([
            'admin_reply' => ['required', 'string', 'max:2000'],
        ]);

        $report->update([
            'admin_reply' => $validated['admin_reply'],
            'status'      => 'read',
            'read_at'     => $report->read_at ?? now(),
        ]);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }
}
